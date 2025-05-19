// Handles loading and submitting file types management inside the modal

document.addEventListener('DOMContentLoaded', function() {
    const fileTypesModal = document.getElementById('fileTypesModal');
    if (!fileTypesModal) return;

    fileTypesModal.addEventListener('show.bs.modal', function() {
        const content = document.getElementById('fileTypesModalContent');
        content.innerHTML = '<div class="text-center py-5"><div class="spinner-border" role="status"></div></div>';
        fetch('/admin/file-types')
            .then(res => res.text())
            .then(html => {
                // Extract only the table/form part from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const card = doc.querySelector('.file-types-card');
                if (card) {
                    content.innerHTML = '';
                    content.appendChild(card);
                } else {
                    content.innerHTML = '<div class="alert alert-danger">Failed to load file types.</div>';
                }
            })
            .catch(() => {
                content.innerHTML = '<div class="alert alert-danger">Failed to load file types.</div>';
            });
    });

    // Optionally, handle closing and refreshing logic here
});
