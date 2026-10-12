document.querySelectorAll('.side-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const subnav = document.getElementById(button.getAttribute('aria-controls'));
        if (!subnav) return;

        subnav.hidden = !subnav.hidden;
        button.setAttribute('aria-expanded', String(!subnav.hidden));
    });
});
