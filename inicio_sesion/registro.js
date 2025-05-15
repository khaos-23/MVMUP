document.getElementById('register-form').addEventListener('submit', function (e) {
    const formData = new FormData(this); 
    const messageDiv = document.getElementById('message'); 
    fetch('registro.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
}); 