function openModal(){ document.getElementById('docModal').hidden = false; }
function closeModal(){ 
  const modal = document.getElementById('docModal');
  if (modal && modal.parentNode) {
    modal.hidden = true; 
  }
}

document.addEventListener('click', (e)=>{
  if(e.target.closest('[data-open-doc]')){
    const btn = e.target.closest('[data-open-doc]');
    const type = btn.getAttribute('data-open-doc');
    const map = {passport:'Паспорт', foreign_passport:'Загранпаспорт', driver_license:'Водительские права'};
    document.getElementById('docTitle').textContent = map[type] || 'Документ';
    document.getElementById('docType').value = type;

    const f = document.getElementById('docForm');
    f.action = f.dataset.store || f.action;
    f.querySelector('[name="_method"]').value = 'POST';
    ['docSeries','docNumber','docIssuedAt','docIssuedBy','docExpiresAt','docComment']
      .forEach(id => { const el = document.getElementById(id); if(el) el.value = ''; });
    openModal();
  }
  if(e.target.closest('[data-edit-doc]')){
    const btn = e.target.closest('[data-edit-doc]');
    const card = btn.closest('.doc-card');
    const json = JSON.parse(card.querySelector('template.payload').innerHTML);
    const map = {passport:'Паспорт', foreign_passport:'Загранпаспорт', driver_license:'Водительские права'};
    document.getElementById('docTitle').textContent = map[json.type] || 'Документ';
    document.getElementById('docType').value = json.type;
    document.getElementById('docSeries').value = json.series || '';
    document.getElementById('docNumber').value = json.number || '';
    document.getElementById('docIssuedAt').value = json.issued_at || '';
    document.getElementById('docIssuedBy').value = json.issued_by || '';
    document.getElementById('docExpiresAt').value = json.expires_at || '';
    document.getElementById('docComment').value = json.comment || '';

    const f = document.getElementById('docForm');
    f.action = f.dataset.baseUpdate.replace('__ID__', json.id);
    f.querySelector('[name="_method"]').value = 'PUT';
    openModal();
  }
  if(e.target.closest('[data-close]')) {
    const closeBtn = e.target.closest('[data-close]');
    const modal = closeBtn.closest('.fixed');
    if (modal && modal.parentNode) {
      modal.classList.add('hidden');
    } else {
      closeModal();
    }
  }
});


