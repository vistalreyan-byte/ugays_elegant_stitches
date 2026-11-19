// Ugay's Elegant Stitches — Global Utilities
document.addEventListener('DOMContentLoaded', function () {
    // Auto fade out success messages after 5 seconds
    const successMsgs = document.querySelectorAll('.msg:not(.error)');
    successMsgs.forEach(function (msg) {
        setTimeout(function () {
            msg.style.transition = 'opacity 0.6s ease';
            msg.style.opacity = '0';
            setTimeout(function () {
                msg.remove();
            }, 600);
        }, 5000);
    });
});
