// venues.js - simple client-side venue selector
const venuesList = document.getElementById('venuesList');
const searchInput = document.getElementById('search');

// Sample venue data using available images V1..V4. Images are reused.
const sampleVenues = [
  { id: 'V1', name: 'Tolip El Narges', address: 'New Cairo, Cairo' , img: '../../public/Photos/V1.png', area: 'New Cairo'},
  { id: 'V2', name: 'Padel Club Central', address: 'Nasr City, Cairo' , img: '../../public/Photos/V2.png', area: 'Nasr City'},
  { id: 'V3', name: 'Green Courts', address: 'Maadi, Cairo' , img: '../../public/Photos/V3.png', area: 'Maadi'},
  { id: 'V4', name: 'Downtown Padel', address: 'Zamalek, Cairo' , img: '../../public/Photos/V4.png', area: 'Zamalek'},
  { id: 'V1-2', name: 'Tolip El Narges 2', address: 'New Cairo Extension' , img: '../../public/Photos/V1.png', area: 'New Cairo'},
  { id: 'V2-2', name: 'Padel Club East', address: 'Nasr City, Cairo' , img: '../../public/Photos/V2.png', area: 'Nasr City'},
  { id: 'V3-2', name: 'Green Courts West', address: 'Maadi, Cairo' , img: '../../public/Photos/V3.png', area: 'Maadi'},
  { id: 'V4-2', name: 'Downtown Padel 2', address: 'Zamalek, Cairo' , img: '../../public/Photos/V4.png', area: 'Zamalek'},
  { id: 'V5', name: 'Riverside Padel', address: 'Imbaba, Cairo', img: '../../public/Photos/V1.png', area: 'Imbaba'},
  { id: 'V6', name: 'Skyline Padel Center', address: 'Heliopolis, Cairo', img: '../../public/Photos/V2.png', area: 'Heliopolis'},
  { id: 'V7', name: 'Desert Padel Arena', address: '6th of October', img: '../../public/Photos/V3.png', area: '6th of October'},
  { id: 'V8', name: 'Coastal Padel Hub', address: 'Alexandria Corniche', img: '../../public/Photos/V4.png', area: 'Alexandria'}
];

function renderList(list){
  venuesList.innerHTML = '';
  if(list.length===0){
    venuesList.innerHTML = '<div class="no-results">No venues found</div>';
    return;
  }

  list.forEach(v=>{
    const card = document.createElement('div');
    card.className = 'venue-card';
    card.innerHTML = `
      <img src="${v.img}" alt="${v.name}">
      <div class="venue-info">
        <h3 class="venue-name">${v.name}</h3>
        <div class="venue-address">${v.address}</div>
      </div>
      <div class="venue-meta">
        <div class="venue-area">${v.area}</div>
        <button class="choose-btn" data-id="${v.id}">Choose</button>
      </div>
    `;

    // clicking card also chooses
    card.querySelector('.choose-btn').addEventListener('click',(e)=>{
      e.stopPropagation();
      chooseVenue(v);
    });
    card.addEventListener('click', ()=>chooseVenue(v));
    venuesList.appendChild(card);
  });
}

function chooseVenue(v){
  // navigate to reservation page with query params using a relative URL
    // navigate to reservation page with query params using a relative URL (works with file://)
    const params = new URLSearchParams();
    params.set('venue', v.id);
    params.set('venueName', v.name);
    params.set('venueAddress', v.address);
    params.set('venueImg', v.img);
  window.location.href = 'court-reservation.php?' + params.toString();
}

searchInput.addEventListener('input', ()=>{
  const q = searchInput.value.trim().toLowerCase();
  if(q==='') renderList(sampleVenues);
  else{
    const filtered = sampleVenues.filter(v=>
      v.name.toLowerCase().includes(q) || v.address.toLowerCase().includes(q) || v.area.toLowerCase().includes(q)
    );
    renderList(filtered);
  }
});

// initial render
renderList(sampleVenues);
