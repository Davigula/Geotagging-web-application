<?php
// Start session BEFORE any HTML output
session_start();
$prijavljen = isset($_SESSION['google_email']);

?>

<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8" />
  <title>GeoTag Otpad</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="hamburger" onclick="toggleSidenav()">☰</div>

<!-- Side Navigation -->
<div class="sidenav" id="sidenav">
  <h3>GeoTag Otpad</h3>
  
  <!-- Status indicator -->
  <?php if ($prijavljen): ?>
    <div class="status-indicator status-logged-in">
      ✓ Prijavljeni ste
    </div>
  <?php else: ?>
    <div class="status-indicator status-logged-out">
      Molimo prijavite se
    </div>
  <?php endif; ?>
  
  <!-- Main Actions -->
  <div class="nav-section">
    <h4>🗂️ Upravljanje</h4>
    
    <?php if ($prijavljen): ?>
      <button onclick="openAddWasteModal()" class="add-waste-btn">
        📍 Dodaj novi otpad
      </button>
      <button onclick="openMyWaste()" class="add-waste-btn"> 
		  📋 Moji prijavljeni otpadi
	  </button>
    <?php else: ?>
      <button disabled style="opacity: 0.6; cursor: not-allowed;" title="Potrebna prijava">
        📍 Dodaj novi otpad
      </button>
    <?php endif; ?>
  </div>
  
  <!-- Filter Section -->
  <div class="nav-section">
    <h4>🔍 Filter</h4>
    <label for="filter">Prikaži po kategoriji:</label>
    <select id="filter">
      <option value="sve">Sve kategorije</option>
      <option value="plastika">🥤 Plastika</option>
      <option value="staklo">🍾 Stoption>
      <option value="elektronika">aklo</option>
      <option value="metal">🥫 Metal<💻 Elektronika></option>
      <option value="opasni">⚠️ Opasni otpad</option>
      <option value="glomazni">🪑 Glomazni otpad</option>
    </select>
  </div>
  
  <!-- Statistics Section -->
  <div class="nav-section">
    <h4>📊 Statistike</h4>
    <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 6px; color: #ecf0f1; font-size: 12px;">
      <div>Ukupno prijava: <span id="total-markers">-</span></div>
      <div>Aktivni filteri: <span id="unique-users">Svi</span></div>
    </div>
  </div>
  
  <!-- Authentication Section -->
  <div class="nav-section" style="margin-top: auto; padding-top: 20px;">
    <h4>👤 Račun</h4>
    <?php if (!$prijavljen): ?>
      <button onclick="window.location.href='google-login/login.php'" class="login-btn">
        🔐 Prijava putem Google
      </button>
    <?php else: ?>
      <button onclick="window.location.href='google-login/logout.php'" class="logout-btn">
        🚪 Odjava
      </button>
    <?php endif; ?>
  </div>
  
  <!-- Help Section -->
  <div class="nav-section">
    <a href="#" onclick="showHelp()" style="font-size: 12px; opacity: 0.8;">
      ❓ Kako dodati otpad?
    </a>
  </div>
</div>

<div id="map"></div>

<!-- Image Modal -->
<div id="imageModal">
  <span id="closeModal" onclick="closeModal()">&times;</span>
  <img id="modalImage" src="" alt="Povećana slika" />
</div>

<!-- Add Waste Modal -->
<div id="addWasteModal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>📍 Dodaj novi otpad</h2>
      <button class="close-btn" onclick="closeAddWasteModal()">&times;</button>
    </div>
    
    <form id="addWasteForm" enctype="multipart/form-data">
      <div class="form-group">
        <label for="wasteImage">📸 Slika otpada <span class="required">*</span></label>
        <div class="file-input-wrapper">
          <input type="file" id="wasteImage" name="image" accept="image/*" required>
          <div class="file-input-display">
            📷 Kliknite za odabir slike ili povucite ovdje
          </div>
        </div>
        <div class="image-preview" id="imagePreview"></div>
      </div>
      
      <div class="form-group">
        <label for="wasteCategory">🗂️ Kategorija otpada <span class="required">*</span></label>
        <select id="wasteCategory" name="category" required>
          <option value="">Odaberite kategoriju...</option>
          <option value="plastika">🥤 Plastika</option>
          <option value="staklo">🍾 Staklo</option>
          <option value="metal">🥫 Metal</option>
          <option value="elektronika">💻 Elektronika</option>
          <option value="opasni">⚠️ Opasni otpad</option>
          <option value="glomazni">🪑 Glomazni otpad</option>
        </select>
      </div>
      
      <div class="form-group">
        <label for="wasteDescription">📝 Opis otpada</label>
        <textarea id="wasteDescription" name="description" placeholder="Opišite vrstu otpada, količinu, stanje..."></textarea>
      </div>
      
      <div class="map-section">
        <h4>🗺️ Odaberite lokaciju <span class="required">*</span></h4>
        <p style="margin: 5px 0; color: #7f8c8d; font-size: 12px;">Kliknite na mapu da označite lokaciju otpada</p>
        <div id="addWasteMap"></div>
        <input type="hidden" id="selectedLat" name="lat" required>
        <input type="hidden" id="selectedLng" name="lng" required>
        <div id="locationStatus" style="margin-top: 10px; padding: 8px; border-radius: 5px; display: none;"></div>
      </div>
      
      <button type="submit" class="submit-btn" id="submitBtn" disabled>
        🚀 Pošalji prijavu
      </button>
    </form>
  </div>
</div>
<div id="myWastePanel" class="modal-overlay" style="display: none;">
    <div class="modal-content" style="max-width: 90%; max-height: 90%; overflow-y: auto;">
        <div class="modal-header">
            <h2>📋 Moji prijavljeni otpadi</h2>
            <button class="close-btn" onclick="closeMyWaste()">&times;</button>
        </div>
        
        <div style="padding: 20px;">
            <table id="myWasteTable" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 10px; border: 1px solid #ddd;">Slika</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Vrsta otpada</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Opis</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Lokacija</th>
                        <th style="padding: 10px; border: 1px solid #ddd;">Datum</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Podaci će se učitati ovdje -->
                </tbody>
            </table>
        </div>
    </div>
</div>
	
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
  const map = L.map("map", {
  center: [45.554962, 18.695514],
  zoom: 10
});

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors"
  }).addTo(map);

  const allMarkers = [];
  let addWasteMap = null;
  let selectedLocationMarker = null;

  function addMarker(lat, lng, imageUrl, description, category, created_at) {
  const popup = `
    <strong>${category.toUpperCase()}</strong><br>
    ${description}<br>
    <small>${new Date(created_at).toLocaleString()}</small><br>
    <img src="${imageUrl}" class="thumbnail" onclick="openModal('${imageUrl}')" />
  `;
  const marker = L.marker([lat, lng]).addTo(map).bindPopup(popup);
  marker.category = category;
  allMarkers.push(marker);
}

  function openModal(imageUrl) {
    document.getElementById("modalImage").src = imageUrl;
    document.getElementById("imageModal").style.display = "flex";
  }

  function closeModal() {
    document.getElementById("imageModal").style.display = "none";
    document.getElementById("modalImage").src = "";
  }

  function loadMarkers() {
  console.log("Pokušavam učitati markere...");

  fetch("get_markers.php")
    .then(res => {
      console.log("Response status:", res.status);
      if (!res.ok) {
        throw new Error('HTTP greška! Status: ' + res.status);
      }
      return res.json();
    })
    .then(data => {
      console.log("Učitani podaci:", data);

      // Provjeri osnovnu strukturu
      if (!data.success || !Array.isArray(data.data)) {
        console.error("Podaci nisu ispravni ili nedostaju:", data);
        return;
      }

      // Isprazni postojeće markere ako ih imaš
      allMarkers.forEach(marker => map.removeLayer(marker));
      allMarkers.length = 0;

      // Dodaj markere
      data.data.forEach(({ lat, lng, image_url, description, category, created_at }) => {
        if (lat && lng) {
          addMarker(lat, lng, image_url, description, category, created_at);
        } else {
          console.warn("Nevažeći marker:", { lat, lng });
        }
      });

      // Ažuriraj broj markera
      document.getElementById("total-markers").textContent = allMarkers.length;

      // Ažuriraj broj korisnika ako postoji
      if (typeof data.unique_emails !== "undefined") {
        document.getElementById("unique-users").textContent =
          data.unique_emails === 1
            ? "1 korisnik"
            : `${data.unique_emails} korisnika`;
      }
    })
    .catch(error => {
      console.error("Greška pri dohvaćanju:", error);
      alert("❌ Greška pri učitavanju markera: " + error.message);
    });
}

  // Filter funkcionalnost
  document.getElementById("filter").addEventListener("change", function () {
    const value = this.value;
    console.log("Filter promijenjen na:", value);
    
    let visibleCount = 0;
    allMarkers.forEach(marker => {
      if (value === "sve" || marker.category === value) {
        if (!map.hasLayer(marker)) {
          map.addLayer(marker);
        }
        visibleCount++;
      } else {
        if (map.hasLayer(marker)) {
          map.removeLayer(marker);
        }
      }
    });
    
    // Update statistics
	document.getElementById("active-filters").textContent = data.unique_emails + " korisnika";
    updateStats();
  });

  // Update statistics function
  function updateStats() {
    document.getElementById("total-markers").textContent = allMarkers.length;
  }

  // Help function
  function showHelp() {
    alert(`Kako dodati novi otpad:

1. 📱 Prijavite se putem Google računa
2. 📍 Kliknite "Dodaj novi otpad" u navigaciji
3. 📸 Uslikajte otpad
4. 🗺️ Označite lokaciju na mapi
5. 📝 Opišite vrstu otpada
6. ✅ Pošaljite prijavu

Vaša prijava će pomoći održavanju čistoće okoliša!`);
  }

  // Add Waste Modal Functions
  function openAddWasteModal() {
    document.getElementById('addWasteModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Initialize map in modal
    setTimeout(() => {
      if (!addWasteMap) {
        addWasteMap = L.map('addWasteMap', {
          center: [44.704330, 16.173273],
          zoom: 7
        });
        
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
          attribution: "&copy; OpenStreetMap contributors"
        }).addTo(addWasteMap);
        
        // Add click event to select location
        addWasteMap.on('click', function(e) {
          const lat = e.latlng.lat;
          const lng = e.latlng.lng;
          
          // Remove previous marker
          if (selectedLocationMarker) {
            addWasteMap.removeLayer(selectedLocationMarker);
          }
          
          // Add new marker
          selectedLocationMarker = L.marker([lat, lng]).addTo(addWasteMap);
          
          // Store coordinates
          document.getElementById('selectedLat').value = lat;
          document.getElementById('selectedLng').value = lng;
          
          // Show location status
          const statusDiv = document.getElementById('locationStatus');
          statusDiv.innerHTML = `✅ Lokacija odabrana: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
          statusDiv.style.display = 'block';
          statusDiv.style.background = '#d4edda';
          statusDiv.style.color = '#155724';
          statusDiv.style.border = '1px solid #c3e6cb';
          
          validateForm();
        });
      } else {
        addWasteMap.invalidateSize();
      }
    }, 100);
  }

  function closeAddWasteModal() {
    document.getElementById('addWasteModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Reset form
    document.getElementById('addWasteForm').reset();
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('locationStatus').style.display = 'none';
    
    // Remove location marker
    if (selectedLocationMarker && addWasteMap) {
      addWasteMap.removeLayer(selectedLocationMarker);
      selectedLocationMarker = null;
    }
    
    // Clear coordinates
    document.getElementById('selectedLat').value = '';
    document.getElementById('selectedLng').value = '';
    
    validateForm();
  }

  // Image preview functionality
  document.getElementById('wasteImage').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
      };
      reader.readAsDataURL(file);
      
      // Update file input display
      document.querySelector('.file-input-display').innerHTML = `📷 ${file.name}`;
    } else {
      preview.innerHTML = '';
      document.querySelector('.file-input-display').innerHTML = '📷 Kliknite za odabir slike ili povucite ovdje';
    }
    
    validateForm();
  });

  // Form validation
  function validateForm() {
    const image = document.getElementById('wasteImage').files[0];
    const category = document.getElementById('wasteCategory').value;
    const lat = document.getElementById('selectedLat').value;
    const lng = document.getElementById('selectedLng').value;
    
	console.log({ image, category, lat, lng });
    const isValid = image && category && lat && lng ;
    document.getElementById('submitBtn').disabled = !isValid;
  }

  // Add event listeners for validation
  document.getElementById('wasteCategory').addEventListener('change', validateForm);

  // Form submission
  document.getElementById('addWasteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = document.getElementById('submitBtn');
    
    // Disable submit button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '⏳ Šalje se...';
    
    fetch('upload.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        alert('✅ Otpad je uspješno prijavljen!');
        closeAddWasteModal();
        loadMarkers(); // Reload markers to show new one
      } else {
        alert('❌ Greška: ' + (data.error || 'Nepoznata greška'));
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('❌ Greška pri slanju prijave');
    })
    .finally(() => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '🚀 Pošalji prijavu';
    });
  });

  // Close modal when clicking outside
  document.getElementById('addWasteModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeAddWasteModal();
    }
  });

  // Učitaj markere kada se mapa potpuno učita
  map.whenReady(function() {
    console.log("Mapa je spremna, učitavam markere...");
    loadMarkers();
  });
	
	

  
function toggleSidenav() {
  const sidenav = document.getElementById('sidenav');
  const hamburger = document.querySelector('.hamburger');
  
  sidenav.classList.toggle('open');
  hamburger.classList.toggle('moved');
}
	
  // Dodajte ovu funkciju unutar <script> tagova

function openMyWaste() {
    const panel = document.getElementById('myWastePanel');
    const tbody = document.querySelector('#myWasteTable tbody');
    
    // Prikaži panel
    panel.style.display = 'block';
    
    // Prikaži loading poruku
    tbody.innerHTML = '<tr><td colspan="5">⏳ Učitavanje...</td></tr>';
    
    // Fetch podatke
    fetch('get-markers.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data); // Debug log
            
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="5">❌ ${data.message || 'Greška u dohvaćanju podataka'}</td></tr>`;
                return;
            }
            
            if (!data.data || data.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5">ℹ️ Nema prijavljenih otpada.</td></tr>`;
                return;
            }
            
            // Prikaži podatke
            tbody.innerHTML = '';
            data.data.forEach(marker => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><img src="${marker.image_url}" width="100" style="width: 100%; height: 50px;  display: block;" ></td>
                    <td>${marker.category}</td>
                    <td>${marker.description || 'Nema opisa'}</td>
                    <td>${parseFloat(marker.lat).toFixed(5)}, ${parseFloat(marker.lng).toFixed(5)}</td>
                    <td>${marker.created_at}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(error => {
            console.error('Error:', error); // Debug log
            tbody.innerHTML = `<tr><td colspan="5">❌ Greška u dohvaćanju podataka: ${error.message}</td></tr>`;
        });
}

// Dodajte funkciju za zatvaranje panela
function closeMyWaste() {
    const panel = document.getElementById('myWastePanel');
    panel.style.display = 'none';
}

// Event listener za zatvaranje panela klikom izvan njega
document.addEventListener('click', function(e) {
    const panel = document.getElementById('myWastePanel');
    if (e.target === panel) {
        closeMyWaste();
    }
});
</script>
</body>
</html>