// Dropdown плавно отваряне
  document.querySelectorAll('.menu > li').forEach(li => {
    li.addEventListener('mouseenter', () => {
      const submenu = li.querySelector('ul');
      if (submenu) submenu.style.display = 'flex';
    });
    li.addEventListener('mouseleave', () => {
      const submenu = li.querySelector('ul');
      if (submenu) submenu.style.display = 'none';
    });
  });

  // Responsive навигация (опционално)
  const navToggle = document.createElement('button');
  navToggle.textContent = "☰";
  navToggle.className = "nav-toggle";
  document.querySelector('.main-nav').prepend(navToggle);

  navToggle.addEventListener('click', () => {
    document.querySelector('.menu').classList.toggle('open');
  });