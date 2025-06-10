function openModal(type, data = null) {
  document.getElementById('modal-todo').classList.remove('hidden');
  document.getElementById('todo-form').style.display = 'block';
  document.getElementById('delete-group').classList.add('hidden');
  document.getElementById('status-group').style.display = 'none';
  document.getElementById('todo-form').reset();
  document.getElementById('todo-id').value = '';
  document.getElementById('todo-title').value = '';
  document.getElementById('todo-desc').value = '';
  document.getElementById('todo-due').value = '';
  document.getElementById('todo-priority').value = ''; // Reset priority
  document.getElementById('modal-submit').innerText = 'Simpan';
  document.getElementById('modal-title').innerText = 'Tambah Todo';

  if (type === 'edit' && data) {
    document.getElementById('modal-title').innerText = 'Edit Todo';
    document.getElementById('todo-id').value = data.id;
    document.getElementById('todo-title').value = data.title;
    document.getElementById('todo-desc').value = data.description;
    document.getElementById('todo-due').value = data.due_date;
    document.getElementById('todo-priority').value = data.priority || ''; // Set priority
    document.getElementById('status-group').style.display = 'block';
    document.getElementById('todo-status').value = data.status;
    document.getElementById('modal-submit').innerText = 'Update';
  }
  if (type === 'delete') {
    document.getElementById('todo-form').style.display = 'none';
    document.getElementById('delete-group').classList.remove('hidden');
    document.getElementById('delete-id').value = data;
  }
}

function closeModal() {
  document.getElementById('modal-todo').classList.add('hidden');
}

// Dropdown toggle script
const btn = document.getElementById('profileDropdownBtn');
const menu = document.getElementById('profileDropdownMenu');
btn.addEventListener('click', function (e) {
  e.stopPropagation();
  menu.classList.toggle('hidden');
});
document.addEventListener('click', function () {
  menu.classList.add('hidden');
});
