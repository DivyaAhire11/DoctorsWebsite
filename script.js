console.log("Script is loaded!");

const menuIcon = document.getElementById('menuIcon');
const menu = document.getElementById('menu');

menuIcon.addEventListener('click', () => {
     menu.classList.toggle('active');
});