let currentPath = ''; 
let showingSharedFiles = false;
let sharedPathStack = []; 
let itemToSharePath = null;
let itemToShareIsFolder = null;


let fileToDeletePath = null;

document.addEventListener('DOMContentLoaded', function () {
  const toggleViewBtn = document.getElementById('toggleViewBtn');
  const localFilesContainer = document.getElementById('localFilesContainer');
  const sharedFilesContainer = document.getElementById('sharedFilesContainer');
  const breadcrumbContainer = document.getElementById('breadcrumbContainer'); 

  toggleViewBtn.addEventListener('click', function () {
    showingSharedFiles = !showingSharedFiles;

    if (showingSharedFiles) {
      localFilesContainer.style.display = 'none';
      sharedFilesContainer.style.display = 'block';
      toggleViewBtn.textContent = 'Ver Archivos Locales';
      if (breadcrumbContainer) breadcrumbContainer.style.display = 'none'; 
      
      const sharedBreadcrumbContainer = document.getElementById('sharedBreadcrumbContainer');
      if (sharedBreadcrumbContainer) sharedBreadcrumbContainer.style.display = 'block';
      loadSharedFiles();
      updateSharedBreadcrumb(); 
    } else {
      sharedFilesContainer.style.display = 'none';
      localFilesContainer.style.display = 'block';
      toggleViewBtn.textContent = 'Ver Archivos Compartidos';
      if (breadcrumbContainer) breadcrumbContainer.style.display = 'block'; 
      
      const sharedBreadcrumbContainer = document.getElementById('sharedBreadcrumbContainer');
      if (sharedBreadcrumbContainer) sharedBreadcrumbContainer.style.display = 'none';
      loadLocalFiles();
    }
  });

  
  const goBackBtn = document.getElementById('goBackBtn');
  if (goBackBtn) {
    goBackBtn.remove();
  }


  const sharedGoBackBtn = document.getElementById('sharedGoBackBtn');
  if (sharedGoBackBtn) {
    sharedGoBackBtn.remove();
  }

  loadLocalFiles();
});


function loadLocalFiles() {
  fetch(`/pagina_almacenamiento/list_files.php?path=${encodeURIComponent(currentPath)}`)
    .then(response => response.json())
    .then(files => {
      updateLocalBreadcrumb(currentPath); 
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
  sharedPathStack = []; 
  updateSharedBreadcrumb();
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
  updateLocalBreadcrumb(currentPath);
}


function shareItem(itemPath, isFolder) {
  itemToSharePath = itemPath;
  itemToShareIsFolder = isFolder;
 
  document.getElementById('recipientEmail').value = '';
  const shareModal = new bootstrap.Modal(document.getElementById('shareModal'));
  shareModal.show();
}

function confirmShare() {
  const recipient = document.getElementById('recipientEmail').value.trim();
  if (!recipient) {
    showUploadNotification('Por favor, introduce el email del destinatario.', false);
    return;
  }

  fetch('/pagina_almacenamiento/share_file.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ file: itemToSharePath, recipient, isFolder: itemToShareIsFolder })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showUploadNotification(data.message || 'Elemento compartido con éxito.', true);
      } else {
        showUploadNotification(data.message || 'Error al compartir el elemento.', false);
      }
      
      const shareModal = bootstrap.Modal.getInstance(document.getElementById('shareModal'));
      if (shareModal) shareModal.hide();
    })
    .catch(error => {
      showUploadNotification('Error al compartir el elemento.', false);
      console.error('Error al compartir el elemento:', error);
      
      const shareModal = bootstrap.Modal.getInstance(document.getElementById('shareModal'));
      if (shareModal) shareModal.hide();
    });
}


function showDeleteConfirmModal(filePath) {
  fileToDeletePath = filePath;
  const modalHtml = `
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Confirmar eliminación</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <p>¿Estás seguro de que quieres eliminar este archivo o carpeta? Todo su contenido será eliminado.</p>
          </div>
          <div class="modal-footer">
            <button type="button" id="cancelDeleteBtn" class="btn btn-danger" style="background-color: #6c757d; border-color: #6c757d;">Cancelar</button>
            <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Eliminar</button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  let existingModal = document.getElementById('deleteConfirmModal');
  if (existingModal) existingModal.remove();
  document.body.insertAdjacentHTML('beforeend', modalHtml);

  const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  deleteConfirmModal.show();

  document.getElementById('confirmDeleteBtn').onclick = function () {
    actuallyDeleteFile();
    deleteConfirmModal.hide();
  };
  document.getElementById('cancelDeleteBtn').onclick = function () {
    fileToDeletePath = null;
  };
}

function actuallyDeleteFile() {
  if (!fileToDeletePath) return;
  fetch('/pagina_almacenamiento/delete_file.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ file: fileToDeletePath })
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        loadLocalFiles();
        showUploadNotification('Archivo o carpeta eliminados con éxito.', true);
      } else {
        showUploadNotification(data.error || 'Error al eliminar el archivo o carpeta.', false);
      }
      fileToDeletePath = null;
    })
    .catch(error => {
      showUploadNotification('Error al eliminar el archivo o carpeta.', false);
      console.error('Error al eliminar el archivo o carpeta:', error);
      fileToDeletePath = null;
    });
}

function deleteFile(filePath) {
  showDeleteConfirmModal(filePath);
}

function createFolder() {
  const folderName = document.getElementById('folderName').value.trim();
  if (!folderName) {
    showUploadNotification('Por favor, introduce un nombre para la carpeta.', false);
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
        showUploadNotification(data.message || 'Carpeta creada con éxito', true);
        document.getElementById('folderName').value = '';
        const modal = bootstrap.Modal.getInstance(document.getElementById('createFolderModal'));
        modal.hide(); 
      } else {
        showUploadNotification(data.message || 'Error al crear la carpeta', false);
      }
    })
    .catch(error => {
      showUploadNotification('Error al crear la carpeta', false);
      console.error('Error:', error);
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
  updateSharedBreadcrumb();
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


function goBackSharedFolder() {
  if (sharedPathStack.length > 0) {
    sharedPathStack.pop();
    const previousPath = sharedPathStack.length > 0 ? sharedPathStack[sharedPathStack.length - 1] : '';
    if (previousPath) {
      enterSharedFolder(previousPath); 
    } else {
      loadSharedFiles(); 
    }
  } else {
    loadSharedFiles(); 
  }
}

function updateLocalBreadcrumb(path) {
  const breadcrumbContainer = document.getElementById('breadcrumbContainer');
  breadcrumbContainer.style.display = showingSharedFiles ? 'none' : 'block'; 

  const breadcrumb = document.getElementById('breadcrumb');
  breadcrumb.innerHTML = '<li class="breadcrumb-item"><a href="#" onclick="navigateToRoot()">Inicio</a></li>';

  if (path) {
    const parts = path.split('/').filter(Boolean);
    
    const showParts = parts.length > 2 ? parts.slice(0, -2) : [];
    let accumulatedPath = '';
    showParts.forEach((part, index) => {
      accumulatedPath += '/' + part;
      breadcrumb.innerHTML += `<li class="breadcrumb-item"><a href="#" onclick="enterFolder('${accumulatedPath}')">${part}</a></li>`;
    });
    
    if (parts.length > 2) {
      breadcrumb.innerHTML += `<li class="breadcrumb-item">...</li>`;
    }
    
    const lastParts = parts.slice(-2);
    lastParts.forEach((part, idx) => {
      accumulatedPath += '/' + part;
      if (idx === lastParts.length - 1) {
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
  updateLocalBreadcrumb(currentPath);
}


function updateSharedBreadcrumb() {
  const sharedBreadcrumbContainer = document.getElementById('sharedBreadcrumbContainer');
  if (!showingSharedFiles) {
    if (sharedBreadcrumbContainer) sharedBreadcrumbContainer.style.display = 'none';
    return;
  }
  if (sharedBreadcrumbContainer) sharedBreadcrumbContainer.style.display = 'block';

  const sharedBreadcrumb = document.getElementById('sharedBreadcrumb');
  sharedBreadcrumb.innerHTML = '<li class="breadcrumb-item"><a href="#" onclick="navigateToSharedRoot()">Inicio</a></li>';

  if (sharedPathStack.length > 0) {
    
    const currentSharedPath = sharedPathStack[sharedPathStack.length - 1];
    const parts = currentSharedPath.split('/').filter(Boolean);

    
    const visibleParts = parts.slice(2);

    
    const showParts = visibleParts.length > 2 ? visibleParts.slice(0, -2) : [];
    let accumulatedPath = parts.slice(0, 2).join('/');

    showParts.forEach((part, index) => {
      accumulatedPath += '/' + part;
      sharedBreadcrumb.innerHTML += `<li class="breadcrumb-item"><a href="#" onclick="goToSharedBreadcrumb('${accumulatedPath}')">${part}</a></li>`;
    });
    if (visibleParts.length > 2) {
      sharedBreadcrumb.innerHTML += `<li class="breadcrumb-item">...</li>`;
    }
   
    const lastParts = visibleParts.slice(-2);
    lastParts.forEach((part, idx) => {
      accumulatedPath += '/' + part;
      if (idx === lastParts.length - 1) {
        sharedBreadcrumb.innerHTML += `<li class="breadcrumb-item active" aria-current="page">${part}</li>`;
      } else {
        sharedBreadcrumb.innerHTML += `<li class="breadcrumb-item"><a href="#" onclick="goToSharedBreadcrumb('${accumulatedPath}')">${part}</a></li>`;
      }
    });
  }
}

function navigateToSharedRoot() {
  sharedPathStack = [];
  loadSharedFiles();
  updateSharedBreadcrumb();
}

function goToSharedBreadcrumb(targetPath) {

  let idx = -1;
  for (let i = 0; i < sharedPathStack.length; i++) {
    if (sharedPathStack[i] === targetPath) {
      idx = i;
      break;
    }
  }
  if (idx !== -1) {
    sharedPathStack = sharedPathStack.slice(0, idx + 1);
    enterSharedFolder(targetPath);
  }
}

function showUploadNotification(message, success = true) {
  const notif = document.getElementById('uploadNotification');
  notif.textContent = message;
  notif.style.display = 'block';
  notif.style.background = success ? '#198754' : '#dc3545';
  notif.style.color = '#fff';
  notif.style.border = '1px solid ' + (success ? '#198754' : '#dc3545');
  notif.style.left = '20px';
  notif.style.top = '80px';
  notif.style.position = 'fixed';
  notif.style.zIndex = 9999;
  notif.style.padding = '10px 20px';
  notif.style.borderRadius = '8px';
  notif.style.minWidth = '180px';
  notif.style.maxWidth = '300px';
  notif.style.fontSize = '0.95rem';
  notif.style.boxShadow = '0 2px 8px rgba(0,0,0,0.15)';
  notif.style.transition = 'transform 0.3s, opacity 0.3s';
  notif.style.transform = 'translateX(-120%)';
  notif.style.opacity = '0.95';

  setTimeout(() => {
    notif.style.transform = 'translateX(0)';
    notif.style.opacity = '1';
  }, 10);

  setTimeout(() => {
    notif.style.transform = 'translateX(-120%)';
    notif.style.opacity = '0.95';
    setTimeout(() => { notif.style.display = 'none'; }, 350);
  }, 2500);
}


document.addEventListener('DOMContentLoaded', function () {
  const uploadForm = document.getElementById('uploadForm');
  if (uploadForm) {
    uploadForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(uploadForm);
      fetch('/pagina_almacenamiento/upload.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showUploadNotification(data.message || 'Archivo subido correctamente.', true);
          loadLocalFiles();
        } else {
          showUploadNotification(data.message || 'Error al subir el archivo.', false);
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
        if (modal) modal.hide();
        uploadForm.reset();
      })
      .catch(() => {
        showUploadNotification('Error al subir el archivo.', false);
      });
    });
  }
});