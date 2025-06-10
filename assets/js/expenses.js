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

function openEditCategoryModal(id, name) {
  document.getElementById('edit_category_id').value = id;
  document.getElementById('edit_category_name').value = name;
  document.getElementById('editCategoryModal').classList.remove('hidden');
}

function closeEditCategoryModal() {
  document.getElementById('editCategoryModal').classList.add('hidden');
}

function openDeleteCategoryModal(id, name) {
  document.getElementById('delete_category_id').value = id;
  document.getElementById('delete_category_name').textContent = name;
  document.getElementById('deleteCategoryModal').classList.remove('hidden');
}

function closeDeleteCategoryModal() {
  document.getElementById('deleteCategoryModal').classList.add('hidden');
}
