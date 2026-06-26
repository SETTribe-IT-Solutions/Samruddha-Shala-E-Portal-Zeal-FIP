<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samruddha Shala E-Portal</title>
    <?php require_once __DIR__ . '/links.php'; ?>
</head>
<body>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.lang-btn');
        const page = document.documentElement;

        function applyLanguage(lang) {
            document.documentElement.setAttribute('data-lang', lang);
            buttons.forEach(btn => btn.classList.toggle('active', btn.getAttribute('data-lang') === lang));
            const elements = document.querySelectorAll('[data-en], [data-mr]');
            elements.forEach(el => {
                const text = lang === 'mr' ? el.getAttribute('data-mr') : el.getAttribute('data-en');
                if (text) {
                    el.textContent = text;
                }
            });
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', function () {
                applyLanguage(this.getAttribute('data-lang'));
            });
        });

        applyLanguage(page.getAttribute('data-lang') || 'en');
    });
</script>
