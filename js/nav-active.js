document.addEventListener('DOMContentLoaded', function() {
    var path = window.location.pathname.split('/').pop().toLowerCase();
    document.querySelectorAll('.nav-btn').forEach(function(a) {
        var href = a.getAttribute('href').toLowerCase();
        if (href === path) a.classList.add('active');
    });
});
