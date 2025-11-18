// reservation.js
// Creates time slots and implements selection logic.

const slotsGrid = document.getElementById('slotsGrid');
const courtsHeader = document.getElementById('courtsHeader');
const summary = document.getElementById('selectionInfo');
const confirmBtn = document.getElementById('confirm');
const dateStrip = document.getElementById('dateStrip');

// venue header elements
const venueImgEl = document.getElementById('venueImg');
const venueNameEl = document.getElementById('venueName');
const venueAddressEl = document.getElementById('venueAddress');
const backBtn = document.getElementById('back');

// date state
// Removed debug logs and unused header parameter code
let dates = [];
let selectedDate = null;

// slot configuration - interval (minutes)
const intervalMinutes = 60; // 1-hour slots as requested
let openMinutes = 9 * 60;  // default 09:00
let closeMinutes = 22 * 60; // default 22:00
let courts = [];
let booked = {}; // { '<court_id>': ['HH:MM', ...] }

// Selection state: { courtId, startIndex, endIndex }
let state = { courtId: null, startIndex: null, endIndex: null };

function timeToLabel(h,m){
  const am = h < 12;
  const displayHour = ((h+11)%12)+1;
  const mm = m.toString().padStart(2,'0');
  return `${displayHour}:${mm} ${am? 'AM' : 'PM'}`; // 12-hour format with AM/PM
}

function buildSlots(){
  slotsGrid.innerHTML = '';
  if(courts.length === 0) {
    return;
  }
  // Generate full day (24h) time slots
  const times = [];
  for(let t = 0; t < 24*60; t += intervalMinutes){
    const h = Math.floor(t/60);
    const m = t % 60;
    times.push([h, m]);
  }
  
  // Set grid columns
  const cols = courts.length;
  if(courtsHeader) courtsHeader.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
  if(slotsGrid) slotsGrid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;

  // helper to check if a minute-of-day is within open range (supports overnight)
  const isWithinOpenRange = (tMin) => {
    if (Number.isFinite(openMinutes) && Number.isFinite(closeMinutes)){
      if (closeMinutes > openMinutes) {
        // normal same-day close
        return tMin >= openMinutes && tMin < closeMinutes;
      } else if (closeMinutes < openMinutes) {
        // crosses midnight: open from openMinutes..1440 and 0..closeMinutes
        return (tMin >= openMinutes) || (tMin < closeMinutes);
      } else {
        // equal times: treat as closed (or 24h open if needed)
        return false;
      }
    }
    return false;
  };

  // Create slot buttons
  times.forEach((t, idx) => {
    courts.forEach((court) => {
      const key = `${t[0].toString().padStart(2,'0')}:${t[1].toString().padStart(2,'0')}`;
      const el = document.createElement('button');
      el.className = 'slot';
      el.dataset.courtId = String(court.court_id);
      el.dataset.index = idx;
      el.dataset.time = key;
      el.innerText = timeToLabel(t[0], t[1]);

      const bookedForCourt = booked[String(court.court_id)] || [];
      const tMinutes = t[0]*60 + t[1];
      const isClosed = !isWithinOpenRange(tMinutes);
      if(isClosed){
        el.classList.add('closed');
        el.setAttribute('aria-disabled','true');
      } else if(bookedForCourt.includes(key)){
        el.classList.add('booked');
        el.setAttribute('aria-disabled','true');
      } else {
        el.classList.add('available');
        if(selectedDate && isDateInPast(selectedDate)){
          el.classList.add('disabled');
          el.setAttribute('aria-disabled','true');
        } else {
          el.addEventListener('click', slotClicked);
        }
      }

      slotsGrid.appendChild(el);
    });
  });

}

function isDateInPast(d){
  const today = new Date();
  // compare by date only
  const a = new Date(d.getFullYear(), d.getMonth(), d.getDate());
  const b = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  return a < b;
}

function slotClicked(e){
  const el = e.currentTarget;
  const courtId = el.dataset.courtId;
  const idx = Number(el.dataset.index);

  // if no court selected yet, start new selection
  if(state.courtId === null || state.courtId !== courtId){
    // start new selection in this court
    state.courtId = courtId;
    state.startIndex = idx;
    state.endIndex = idx+1; // end is exclusive index: start->start+1 means 30min
  } else {
    // same court: if idx < startIndex then expand backwards, else set endIndex to idx+1
    if(idx < state.startIndex){
      state.startIndex = idx;
    }
    state.endIndex = idx+1;
  }

  // normalize so endIndex > startIndex
  if(state.endIndex <= state.startIndex){
    state.endIndex = state.startIndex + 1;
  }

  // check for booked conflict between startIndex..endIndex-1 for this court
  const conflict = hasBookedBetween(state.courtId, state.startIndex, state.endIndex);
  if(conflict){
    // if conflict, reset selection to only clicked slot (if it's not booked)
    state.startIndex = idx;
    state.endIndex = idx+1;
    if(hasBookedBetween(state.courtId, state.startIndex, state.endIndex)){
      // can't select
      state = { courtId: null, startIndex: null, endIndex: null };
    }
  }

  renderSelection();
}

function hasBookedBetween(courtId, startIdx, endIdx){
  // iterate elements with matching dataset.court and index in range
  const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.courtId===String(courtId));
  for(let i=startIdx;i<endIdx;i++){
    const node = nodes[i];
    if(!node) continue;
    if(node.classList.contains('booked') || node.classList.contains('closed')) return true;
  }
  return false;
}

function renderSelection(){
  // clear previous markings
  Array.from(slotsGrid.children).forEach(n=>{
    n.classList.remove('selected','in-range');
  });

  if(!state.courtId){
    summary.innerText = 'No selection';
    confirmBtn.disabled = true;
    return;
  }

  const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.courtId===String(state.courtId));
  for(let i=0;i<nodes.length;i++){
    if(i>=state.startIndex && i<state.endIndex){
      // mark first slot as selected, rest as in-range
      if(i===state.startIndex) nodes[i].classList.add('selected');
      else nodes[i].classList.add('in-range');
    }
  }

  // compute labels
  const startNode = nodes[state.startIndex];
  const endNode = nodes[state.endIndex-1];
  if(!startNode || !endNode){
    summary.innerText = 'Invalid selection';
    confirmBtn.disabled = true;
    return;
  }

  const startLabel = startNode.innerText;
  // end time is endNode time + interval
  const [h,m] = endNode.dataset.time.split(':').map(Number);
  const endDate = new Date(0,0,0,h,m);
  endDate.setMinutes(endDate.getMinutes()+intervalMinutes);
  const endLabel = timeToLabel(endDate.getHours(), endDate.getMinutes()); // 12-hour format with AM/PM

  const cName = getCourtNameById(state.courtId);
  summary.innerText = `${cName} — ${startLabel} to ${endLabel}`;
  confirmBtn.disabled = false;
}

confirmBtn.addEventListener('click', async ()=>{
  if(!state.courtId || !selectedDate) return;
  confirmBtn.disabled = true;
  try{
    const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.courtId===String(state.courtId));
    const startNode = nodes[state.startIndex];
    const endNode = nodes[state.endIndex-1];
    if(!startNode || !endNode) return;
    const startTime = startNode.dataset.time; // HH:MM
    const [eh,em] = endNode.dataset.time.split(':').map(Number);
    const endDate = new Date(0,0,0,eh,em);
    endDate.setMinutes(endDate.getMinutes()+intervalMinutes);
    const endTime = `${endDate.getHours().toString().padStart(2,'0')}:${endDate.getMinutes().toString().padStart(2,'0')}`;

    const iso = selectedDate.toISOString().slice(0,10);
    const res = await fetch('/PadelUp/public/api/bookings.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        venue_id: (window.venueConfig && window.venueConfig.id) ? Number(window.venueConfig.id) : 0,
        court_id: Number(state.courtId),
        date: iso,
        start_time: startTime,
        end_time: endTime
      })
    });
    if(!res.ok){
      const err = await res.json().catch(()=>({error:'Unknown error'}));
      alert(err.error || 'Failed to create booking');
      confirmBtn.disabled = false;
      return;
    }

    // Read result to get total_price
    const result = await res.json().catch(()=>({}));
    const price = typeof result.total_price !== 'undefined' ? Number(result.total_price) : null;

    // Show modal with confirmation text (include price if available)
    const modal = document.getElementById('confirmModal');
    const modalText = document.getElementById('modalText');
    const rangeText = `${getCourtNameById(state.courtId)} reserved from ${startNode.innerText} to ${timeToLabel(endDate.getHours(), endDate.getMinutes())}`;
    modalText.innerText = price != null && !Number.isNaN(price)
      ? `${rangeText}. Total: ${price.toFixed(2)}`
      : rangeText;
    modal.setAttribute('aria-hidden','false');

    // After booking, reload booked slots for this date
    await loadBookingsForDate(selectedDate);
    // Reset selection and refresh grid so booked slots go red
    state = { courtId: null, startIndex: null, endIndex: null };
    renderSelection();
    buildSlots();
  }catch(e){
    alert('Failed to create booking');
  } finally {
    confirmBtn.disabled = false;
  }
});

// modal close handler: mark slots as booked
document.getElementById('modalClose').addEventListener('click', ()=>{
  const modal = document.getElementById('confirmModal');
  modal.setAttribute('aria-hidden','true');

  // Mark the selected slots in `booked` so they become red and disabled
  if(state.courtId && state.startIndex!=null && state.endIndex!=null){
    // ensure array exists
    if(!booked[String(state.courtId)]) booked[String(state.courtId)]=[];
    const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.courtId===String(state.courtId));
    for(let i=state.startIndex;i<state.endIndex;i++){
      const node = nodes[i];
      if(!node) continue;
      const t = node.dataset.time;
      if(!booked[String(state.courtId)].includes(t)) booked[String(state.courtId)].push(t);
    }

    // rebuild slots and clear selection
    state = { courtId: null, startIndex: null, endIndex: null };
    renderSelection();
    buildSlots();
  }
});
// Date helper: generate dates centered around today
// removed unused generateDates helper

function renderDateStrip(){
  dateStrip.innerHTML = '';
  dates.forEach(d=>{
    const el = document.createElement('button');
    el.className = 'date';
    const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    el.innerHTML = `${dayNames[d.getDay()]}<br><span class="day">${d.getDate()}</span><br><small>${monthNames[d.getMonth()]}</small>`;
    el.dataset.iso = d.toISOString().slice(0,10);
    if(isDateInPast(d)){
      el.classList.add('disabled');
      el.setAttribute('aria-disabled','true');
    }
    if(selectedDate && d.toISOString().slice(0,10) === selectedDate.toISOString().slice(0,10)){
      el.classList.add('selected');
    }
    el.addEventListener('click', ()=>onDateClick(d));
    dateStrip.appendChild(el);
  });
}

async function onDateClick(d){
  // don't allow selecting past dates
  if(isDateInPast(d)) return;
  selectedDate = d;
  // update visuals
  renderDateStrip();

  // Load real bookings for this date
  await loadBookingsForDate(d);
  // reset selection state when date changes
  state = { courtId: null, startIndex: null, endIndex: null };
  renderSelection();
}

function getCourtNameById(id){
  const c = courts.find(c=>String(c.court_id)===String(id));
  return c ? c.court_name : `Court ${id}`;
}

function renderCourtsHeader(){
  if(!courtsHeader) return;
  courtsHeader.innerHTML = '';
  courts.forEach(c=>{
    const el = document.createElement('div');
    el.className = 'court-title';
    el.innerText = c.court_name;
    courtsHeader.appendChild(el);
  });
}

async function loadBookingsForDate(d){
  const iso = d.toISOString().slice(0,10);
  try{
    const params = new URLSearchParams(window.location.search);
    const venueId = (window.venueConfig && window.venueConfig.id)
      ? Number(window.venueConfig.id)
      : Number(params.get('venue_id') || 0);
    if(!venueId || Number.isNaN(venueId)){
      return;
    }
    const res = await fetch(`/PadelUp/public/api/bookings.php?venue_id=${venueId}&date=${iso}`);
    if(!res.ok) throw new Error('Failed to load bookings');
    const json = await res.json();
    
    courts = Array.isArray(json.courts) ? json.courts : [];
    booked = (json.bookings && typeof json.bookings === 'object') ? json.bookings : {};
    
    // Extract venue hours (including minutes) and compute minute offsets
    // reset hours before applying new values to avoid carryover across venues
    openMinutes = NaN; closeMinutes = NaN;
    if (json.hours) {
      if (json.hours.opening_time) {
        const parts = String(json.hours.opening_time).split(':').map(Number);
        openMinutes = (parts[0]*60) + (parts[1] || 0);
      }
      if (json.hours.closing_time) {
        const parts = String(json.hours.closing_time).split(':').map(Number);
        closeMinutes = (parts[0]*60) + (parts[1] || 0);
      }
    }
    // Defaults and validation
    if(!(Number.isFinite(openMinutes) && Number.isFinite(closeMinutes) && (closeMinutes !== openMinutes))) {
      openMinutes = 9*60;
      closeMinutes = 22*60;
    }
    renderCourtsHeader();
    buildSlots();
  }catch(err){
    courts = [];
    booked = {};
  }
}

// initialize date strip and default selection
// Show only today through today + 7 days (1 week ahead)
const today = new Date();
dates = [];
for(let i=0;i<=7;i++){
  const d = new Date(today);
  d.setDate(today.getDate() + i);
  dates.push(d);
}
selectedDate = new Date(today);
renderDateStrip();
loadBookingsForDate(selectedDate);

// Read venue query params and populate header
(function applyVenueFromQuery(){
  try{
    const params = new URLSearchParams(window.location.search);
    const name = params.get('venueName');
    const address = params.get('venueAddress');
    const img = params.get('venueImg');
    if(name && venueNameEl) venueNameEl.innerText = decodeURIComponent(name);
    if(address && venueAddressEl) venueAddressEl.innerText = decodeURIComponent(address);
    if(img && venueImgEl) venueImgEl.src = decodeURIComponent(img);
  }catch(e){
    // ignore if no params
  }
})();

if(backBtn){
  backBtn.addEventListener('click', ()=>{
    // Use root-relative file in same folder as this page
    window.location.href = 'venues.php';
  });
}