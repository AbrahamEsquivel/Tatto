/*=============== MOSTRAR MENU ===============*/
const showMenu = (toggleId, navId) => {
    const toggle = document.getElementById(toggleId),
        nav = document.getElementById(navId);

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('show-menu');
            toggle.classList.toggle('show-icon');
        });
    }
};

showMenu('nav-toggle', 'nav-menu');

/*=============== MOSTRAR/SUBMENU ===============*/
const subMenuItems = document.querySelectorAll('.menu_desplegable__item'); // Selecciona los elementos con submenús

subMenuItems.forEach((item) => {
    const subMenu = item.querySelector('.menu_desplegable__menu'); // Encuentra el submenú correspondiente
    const arrow = item.querySelector('.menu_desplegable__arrow'); // Encuentra el ícono de flecha

    item.addEventListener('click', () => {
        // Alterna la clase que despliega el submenú
        subMenu.classList.toggle('show-submenu');
        arrow.classList.toggle('rotate-arrow');
    });
});