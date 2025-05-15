let currentPath = ''; 
let showingSharedFiles = false;
let sharedPathStack = []; 

document.addEventListener('DOMContentLoaded', function () {
  const toggleViewBtn = document.getElementById('toggleViewBtn');
  const localFilesContainer = document.getElementById('localFilesContainer');
  const sharedFilesContainer = document.getElementById('sharedFilesContainer');

 
  toggleViewBtn.addEventListener('click', function () {
    showingSharedFiles = !showingSharedFiles;

    if (showingSharedFiles) {
      localFilesContainer.style.display = 'none';
      sharedFilesContainer.style.display = 'block';
      toggleViewBtn.textContent = 'Ver Archivos Locales';
      loadSharedFiles();
    } else {
      sharedFilesContainer.style.display = 'none';
      localFilesContainer.style.display = 'block';
      toggleViewBtn.textContent = 'Ver Archivos Compartidos';
      loadLocalFiles();
    }
  });



  loadLocalFiles();

  const urlParams = new URLSearchParams(window.location.search);
  const message = urlParams.get('message');
  const type = urlParams.get('type');

  if (message) {
    const messageContainer = document.getElementById('messageContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    messageContainer.appendChild(alertDiv);

    // Eliminar parámetros de la URL
    history.replaceState(null, '', window.location.pathname);
  }
});


function loadLocalFiles() {
  fetch(`/pagina_almacenamiento/list_files.php?path=${encodeURIComponent(currentPath)}`)
    .then(response => response.json())
    .then(files => {
      updateBreadcrumb(currentPath);
      const localFileList = document.getElementById('localFileList');
      localFileList.innerHTML = '';

      if (files.error) {
        localFileList.innerHTML = `<li class="list-group-item text-danger">${files.error}</li>`;
        return;
      }

      files.forEach(file => {
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

        if (file.is_dir) {
          listItem.innerHTML = `
            <span class="folder-name" style="cursor: pointer;" onclick="enterFolder('${file.path}')">
              <i class="fas fa-folder text-warning me-2"></i>${file.name}
            </span>
            <div>
              <button class="btn btn-sm btn-primary" onclick="shareItem('${file.path}', true)">Compartir</button>
              <button class="btn btn-sm btn-danger" onclick="deleteFile('${file.path}')">Eliminar</button>
            </div>
          `;
        } else {
          listItem.innerHTML = `
            <span>
              <i class="fas fa-file text-secondary me-2"></i>${file.name}
            </span>
            <div>
              <a href="/pagina_almacenamiento/download_local.php?file=${encodeURIComponent(file.path)}" class="btn btn-sm btn-success" download="${file.name}">Descargar</a>
              <button class="btn btn-sm btn-primary" onclick="shareItem('${file.path}', false)">Compartir</button>
              <button class="btn btn-sm btn-danger" onclick="deleteFile('${file.path}')">Eliminar</button>
            </div>
          `;
        }

        localFileList.appendChild(listItem);
      });
    })
    .catch(error => {
      console.error('Error al cargar los archivos locales:', error);
      document.getElementById('localFileList').innerHTML = `<li class="list-group-item text-danger">Error al cargar los archivos locales.</li>`;
    });
}


function loadSharedFiles() {
  fetch('/pagina_almacenamiento/list_shared_folders.php')
    .then(response => response.json())
    .then(items => {
      const sharedFileList = document.getElementById('sharedFileList');
      sharedFileList.innerHTML = '';

      if (items.error) {
        sharedFileList.innerHTML = `<li class="list-group-item text-danger">${items.error}</li>`;
        return;
      }

      items.forEach(item => {
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

        if (item.is_dir) {
          listItem.innerHTML = `
            <span class="folder-name" style="cursor: pointer;" onclick="enterSharedFolder('${item.path}')">
              <i class="fas fa-folder text-warning me-2"></i>${item.name}
            </span>
          `;
        } else {
          listItem.innerHTML = `
            <span>
              <i class="fas fa-file text-secondary me-2"></i>${item.name}
            </span>
            <div>
              <a href="/pagina_almacenamiento/download.php?file=${encodeURIComponent(item.path)}" class="btn btn-sm btn-success" download>Descargar</a>
            </div>
          `;
        }

        sharedFileList.appendChild(listItem);
      });
    })
    .catch(error => {
      console.error('Error al cargar los archivos compartidos:', error);
      const sharedFileList = document.getElementById('sharedFileList');
      sharedFileList.innerHTML = `<li class="list-group-item text-danger">Error al cargar los archivos compartidos.</li>`;
    });
}

function enterFolder(folderPath) {
  currentPath = folderPath;
  loadLocalFiles();
  document.getElementById('uploadPath').value = currentPath; 
  updateBreadcrumb(currentPath);
}

function shareItem(itemPath, isFolder) {
    const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
    document.getElementById('recipientEmail').value = ''; // Limpiar el campo de correo
    document.getElementById('shareFileForm').onsubmit = function (e) {
        e.preventDefault();
        const recipient = document.getElementById('recipientEmail').value;
        if (!recipient) return;

        fetch('/pagina_almacenamiento/share_file.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ file: itemPath, recipient, isFolder })
        })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url; // Redirigir para mostrar el mensaje
                } else {
                    return response.json();
                }
            })
            .catch(error => console.error('Error al compartir el elemento:', error));
    };
    shareModal.show();
}

function deleteFile(filePath) {
  fetch('/pagina_almacenamiento/delete_file.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ file: filePath })
  })
    .then(response => response.json())
    .then(data => {
      const messageContainer = document.getElementById('messageContainer');
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show`;
      alertDiv.role = 'alert';
      alertDiv.innerHTML = `
        ${data.message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      messageContainer.appendChild(alertDiv);

      if (data.success) {
        loadLocalFiles(); // Recargar la lista de archivos locales
      }
    })
    .catch(error => console.error('Error al eliminar el archivo o carpeta:', error));
}

function createFolder() {
  const folderName = document.getElementById('folderName').value.trim();
  if (!folderName) {
    alert('Por favor, introduce un nombre para la carpeta.');
    return;
  }
  fetch('/pagina_almacenamiento/create_folder.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ folder: currentPath + '/' + folderName })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadLocalFiles();
        document.getElementById('folderName').value = '';
        const modal = bootstrap.Modal.getInstance(document.getElementById('createFolderModal'));
        modal.hide();
      }
    })
    .catch(error => {

    });
}

document.addEventListener('DOMContentLoaded', function () {
  const sharedFolderList = document.getElementById('sharedFolderList');

  fetch('/pagina_almacenamiento/list_shared_folders.php')
    .then(response => response.json())
    .then(folders => {
      if (folders.error) {
        sharedFolderList.innerHTML = `<li class="list-group-item text-danger">${folders.error}</li>`;
        return;
      }

      if (folders.length === 0) {
        sharedFolderList.innerHTML = `<li class="list-group-item text-warning">No hay carpetas compartidas disponibles.</li>`;
        return;
      }

      folders.forEach(folder => {
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item';
        listItem.innerHTML = `
          <a href="${folder.path}" target="_blank">${folder.name}</a>
        `;
        sharedFolderList.appendChild(listItem);
      });
    })
    .catch(error => {
      console.error('Error al cargar las carpetas compartidas:', error);
      sharedFolderList.innerHTML = `<li class="list-group-item text-danger">Error al cargar las carpetas compartidas.</li>`;
    });
});


function enterSharedFolder(folderPath) {
  sharedPathStack.push(folderPath); 
  fetch(`/pagina_almacenamiento/list_shared_files.php?path=${encodeURIComponent(folderPath)}`)
    .then(response => response.json())
    .then(files => {
      const sharedFileList = document.getElementById('sharedFileList');
      sharedFileList.innerHTML = '';

      if (files.error) {
        sharedFileList.innerHTML = `<li class="list-group-item text-danger">${files.error}</li>`;
        return;
      }

      files.forEach(file => {
        const listItem = document.createElement('li');
        listItem.className = 'list-group-item d-flex justify-content-between align-items-center';

        if (file.is_dir) {
          listItem.innerHTML = `
            <span class="folder-name" style="cursor: pointer;" onclick="enterSharedFolder('${file.path}')">
              <i class="fas fa-folder text-warning me-2"></i>${file.name}
            </span>
          `;
        } else {
          listItem.innerHTML = `
            <span>
              <i class="fas fa-file text-secondary me-2"></i>${file.name}
            </span>
            <div>
              <a href="/pagina_almacenamiento/download.php?file=${encodeURIComponent(file.path)}" class="btn btn-sm btn-success" download>Descargar</a>
            </div>
          `;
        }

        sharedFileList.appendChild(listItem);
      });
    })
    .catch(error => {
      console.error('Error al listar los contenidos de la carpeta compartida:', error);
      document.getElementById('sharedFileList').innerHTML = `<li class="list-group-item text-danger">Error al listar los contenidos de la carpeta compartida.</li>`;
    });
}



function updateBreadcrumb(path) {
  const breadcrumb = document.getElementById('breadcrumb');
  breadcrumb.innerHTML = '<li class="breadcrumb-item"><a href="#" onclick="navigateToRoot()">Inicio</a></li>';

  if (path) {
    const parts = path.split('/').filter(Boolean);
    let accumulatedPath = '';

    parts.forEach((part, index) => {
      accumulatedPath += '/' + part;
      if (index === parts.length - 1) {
        breadcrumb.innerHTML += `<li class="breadcrumb-item active" aria-current="page">${part}</li>`;
      } else {
        breadcrumb.innerHTML += `<li class="breadcrumb-item"><a href="#" onclick="enterFolder('${accumulatedPath}')">${part}</a></li>`;
      }
    });
  }
}

function navigateToRoot() {
  currentPath = '';
  loadLocalFiles();
  updateBreadcrumb(currentPath);
}