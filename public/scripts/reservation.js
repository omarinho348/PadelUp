// reservation.js
// Creates time slots for two courts and implements selection logic.

const slotsGrid = document.getElementById('slotsGrid');
const summary = document.getElementById('selectionInfo');
const confirmBtn = document.getElementById('confirm');
const dateStrip = document.getElementById('dateStrip');

// venue header elements (may not be present if user didn't add params)
const venueImgEl = document.getElementById('venueImg');
const venueNameEl = document.getElementById('venueName');
const venueAddressEl = document.getElementById('venueAddress');
const backBtn = document.getElementById('back');

// date state
let dates = []; // array of Date objects shown in the strip
let selectedDate = null; // Date object

// configuration
const startHour = 10; // 10:00 AM
const endHour = 18; // 6:00 PM
const intervalMinutes = 30;
const courts = ['A','B'];

// Booked slots (will be replaced per-date). Shape: { 'A': ['10:00', ...], 'B': [...] }
let booked = { 'A': [], 'B': [] };

// State for selections: { court: 'A', start: '13:00', end: '13:30' }
let state = { court: null, startIndex: null, endIndex: null };

function timeToLabel(h,m){
  const am = h < 12;
  const displayHour = ((h+11)%12)+1;
  const mm = m.toString().padStart(2,'0');
  return `${displayHour}:${mm} ${am? 'AM' : 'PM'}`;
}

function buildSlots(){
  // clear existing
  slotsGrid.innerHTML = '';

  const times = [];
  for(let h=startHour; h<endHour; h++){
    times.push([h,0]);
    times.push([h,30]);
  }

  // create columns for each court, but interleave so grid shows two columns
  times.forEach((t,idx)=>{
    courts.forEach((court)=>{
      const key = `${t[0].toString().padStart(2,'0')}:${t[1].toString().padStart(2,'0')}`;
      const el = document.createElement('button');
      el.className = 'slot';
      el.dataset.court = court;
      el.dataset.index = idx;
      el.dataset.time = key;
      el.innerText = timeToLabel(t[0],t[1]);

      // mark booked
      if(booked[court] && booked[court].includes(key)){
        el.classList.add('booked');
        el.setAttribute('aria-disabled','true');
      } else {
        el.classList.add('available');
        // If selectedDate is in the past, disable click
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
  const court = el.dataset.court;
  const idx = Number(el.dataset.index);

  // if no court selected yet, start new selection
  if(state.court === null || state.court !== court){
    // start new selection in this court
    state.court = court;
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
  const conflict = hasBookedBetween(state.court, state.startIndex, state.endIndex);
  if(conflict){
    // if conflict, reset selection to only clicked slot (if it's not booked)
    state.startIndex = idx;
    state.endIndex = idx+1;
    if(hasBookedBetween(state.court, state.startIndex, state.endIndex)){
      // can't select
      state = { court: null, startIndex: null, endIndex: null };
    }
  }

  renderSelection();
}

function hasBookedBetween(court, startIdx, endIdx){
  // iterate elements with matching dataset.court and index in range
  const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.court===court);
  for(let i=startIdx;i<endIdx;i++){
    const node = nodes[i];
    if(!node) continue;
    if(node.classList.contains('booked')) return true;
  }
  return false;
}

function renderSelection(){
  // clear previous markings
  Array.from(slotsGrid.children).forEach(n=>{
    n.classList.remove('selected','in-range');
  });

  if(!state.court){
    summary.innerText = 'No selection';
    confirmBtn.disabled = true;
    return;
  }

  const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.court===state.court);
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
  const endLabel = `${((endDate.getHours()+11)%12)+1}:${endDate.getMinutes().toString().padStart(2,'0')} ${endDate.getHours()<12? 'AM' : 'PM'}`;

  summary.innerText = `Court ${state.court} — ${startLabel} to ${endLabel}`;
  confirmBtn.disabled = false;
}

confirmBtn.addEventListener('click', ()=>{
  if(!state.court) return;
  // Show modal with confirmation text
  const modal = document.getElementById('confirmModal');
  const modalText = document.getElementById('modalText');

  // compute booking start and end times for message
  const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.court===state.court);
  const startNode = nodes[state.startIndex];
  const endNode = nodes[state.endIndex-1];
  let startLabel = startNode ? startNode.innerText : '';
  let endLabel = '';
  if(endNode){
    const [h,m] = endNode.dataset.time.split(':').map(Number);
    const endDate = new Date(0,0,0,h,m);
    endDate.setMinutes(endDate.getMinutes()+intervalMinutes);
    endLabel = `${((endDate.getHours()+11)%12)+1}:${endDate.getMinutes().toString().padStart(2,'0')} ${endDate.getHours()<12? 'AM' : 'PM'}`;
  }

  modalText.innerText = `Court ${state.court} reserved from ${startLabel} to ${endLabel}`;
  modal.setAttribute('aria-hidden','false');
});

// modal close handler: mark slots as booked
document.getElementById('modalClose').addEventListener('click', ()=>{
  const modal = document.getElementById('confirmModal');
  modal.setAttribute('aria-hidden','true');

  // Mark the selected slots in `booked` so they become red and disabled
  if(state.court && state.startIndex!=null && state.endIndex!=null){
    // ensure array exists
    if(!booked[state.court]) booked[state.court]=[];
    const nodes = Array.from(slotsGrid.children).filter(n=>n.dataset.court===state.court);
    for(let i=state.startIndex;i<state.endIndex;i++){
      const node = nodes[i];
      if(!node) continue;
      const t = node.dataset.time;
      if(!booked[state.court].includes(t)) booked[state.court].push(t);
    }

    // rebuild slots and clear selection
    state = { court: null, startIndex: null, endIndex: null };
    renderSelection();
    buildSlots();
  }
});
// Date helper: generate dates centered around today
function generateDates(center, range){
  const res = [];
  for(let offset=-range; offset<=range; offset++){
    const d = new Date(center);
    d.setDate(center.getDate() + offset);
    res.push(d);
  }
  return res;
}

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

  // fetch bookings for date (try real API, else fallback to mock)
  await loadBookingsForDate(d);
  // reset selection state when date changes
  state = { court: null, startIndex: null, endIndex: null };
  renderSelection();
  buildSlots();
}

// Mocked booking data for demo per ISO date
const mockBookings = {
  // today example will use the original booked pattern
};

async function loadBookingsForDate(d){
  const iso = d.toISOString().slice(0,10);
  // Try fetching from a real endpoint (if exists). If fetch fails, fall back to mock.
  try{
    const res = await fetch(`/api/bookings?date=${iso}`);
    if(!res.ok) throw new Error('no api');
    const json = await res.json();
    booked = json;
  }catch(err){
    // fallback: simple mocked rules
    // - if date is today, mark morning slots booked (demo)
    // - if date is weekend, mark afternoon booked on court B
    const today = new Date();
    const isoToday = today.toISOString().slice(0,10);
    if(iso === isoToday){
      booked = { 'A': ['10:00','10:30','11:00','11:30','12:00','12:30'], 'B': ['10:00','10:30','11:00','11:30','12:00','12:30'] };
    } else {
      const dow = d.getDay();
      if(dow === 0 || dow === 6){
        booked = { 'A': [], 'B': ['13:00','13:30','14:00'] };
      } else {
        booked = { 'A': [], 'B': [] };
      }
    }
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
loadBookingsForDate(selectedDate).then(()=>{
  buildSlots();
});

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

