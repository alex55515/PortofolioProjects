let _token = localStorage.getItem("token") || "";
let _role = localStorage.getItem("role") || "";
let _name = localStorage.getItem("name") || "";
let _selectedFile = null;
let _selectedStudentId = null;
let _selectedStudentName = "";
let _reviewAdvId = null;
let _allStudents = [];
let _myAdvPage = 1;
let _allAdvPage = 1;
let _studentAdvPage = 1;
let _studentsListPage = 1;

(function init() {
  if (_token) {
    verifyAndInit();
  }
})();

async function verifyAndInit() {
  try {
    const response = await api("GET", "/api/me");
    if (response.error) {
      clearAuth();
      resetAuthForms();
      showPage("login");
      return;
    }

    _role = response.role;
    _name = response.nume;
    localStorage.setItem("role", _role);
    localStorage.setItem("name", _name);
    showApp();
  } catch (error) {
    clearAuth();
    resetAuthForms();
    showPage("login");
  }
}

function showApp() {
  if (_role === "student") {
    document.getElementById("hdr-student-name").textContent = _name;
    showPage("student");
    loadMyAdeverinte(1);
    return;
  }

  document.getElementById("hdr-sec-name").textContent = _name;
  showPage("secretariat");
  loadStats();
  loadStudents();
  loadGrupe();
  loadAllAdeverinte(1);
}

function switchAuthTab(tab) {
  const buttons = document.querySelectorAll(".tabs-login button");
  buttons[0].classList.toggle("active", tab === "login");
  buttons[1].classList.toggle("active", tab === "register");
  document.getElementById("login-form").style.display = tab === "login" ? "block" : "none";
  document.getElementById("register-form").style.display = tab === "register" ? "block" : "none";
  document.getElementById("login-error").style.display = "none";
}

async function doLogin() {
  const username = document.getElementById("inp-user").value.trim();
  const password = document.getElementById("inp-pass").value;

  if (!username || !password) {
    return showErr("Completati toate campurile.");
  }

  try {
    const response = await apiRaw("POST", "/api/login", { username, password });
    if (response.error) {
      return showErr(response.error);
    }

    _token = response.token;
    _role = response.role;
    _name = response.nume;
    localStorage.setItem("token", _token);
    localStorage.setItem("role", _role);
    localStorage.setItem("name", _name);
    resetAuthForms();
    showApp();
  } catch (error) {
    showErr("Eroare de conexiune.");
  }
}

async function doRegister() {
  const nume = document.getElementById("reg-nume").value.trim();
  const grupa = document.getElementById("reg-grupa").value.trim();
  const user = document.getElementById("reg-user").value.trim();
  const pass = document.getElementById("reg-pass").value;

  if (!nume || !user || !pass) {
    return showErr("Completati campurile obligatorii.");
  }
  if (pass.length < 6) {
    return showErr("Parola trebuie sa aiba minim 6 caractere.");
  }

  try {
    const response = await apiRaw("POST", "/api/register", {
      username: user,
      password: pass,
      nume,
      grupa,
    });
    if (response.error) {
      return showErr(response.error);
    }

    toast("Cont creat. Autentificati-va.", "success");
    resetAuthForms();
    switchAuthTab("login");
    document.getElementById("inp-user").value = user;
  } catch (error) {
    showErr("Eroare de conexiune.");
  }
}

async function doLogout() {
  try {
    if (_token) {
      await apiRaw("POST", "/api/logout");
    }
  } catch (error) {
  }

  clearAuth();
  resetAuthForms();
  clearFile();
  closeModal();
  showPage("login");
  switchAuthTab("login");
  toast("V-ati deconectat.", "info");
}

function clearAuth() {
  _token = "";
  _role = "";
  _name = "";
  _selectedStudentId = null;
  _selectedStudentName = "";
  _myAdvPage = 1;
  _allAdvPage = 1;
  _studentAdvPage = 1;
  _studentsListPage = 1;
  localStorage.removeItem("token");
  localStorage.removeItem("role");
  localStorage.removeItem("name");
}

function resetAuthForms() {
  [
    "inp-user",
    "inp-pass",
    "reg-nume",
    "reg-grupa",
    "reg-user",
    "reg-pass",
  ].forEach((id) => {
    const field = document.getElementById(id);
    if (field) {
      field.value = "";
    }
  });
  document.getElementById("login-error").style.display = "none";
}

function showErr(message) {
  const errorBox = document.getElementById("login-error");
  errorBox.textContent = message;
  errorBox.style.display = "block";
}

document.getElementById("inp-pass").addEventListener("keydown", (event) => {
  if (event.key === "Enter") {
    doLogin();
  }
});

function onDragOver(event) {
  event.preventDefault();
  document.getElementById("drop-area").classList.add("drag-over");
}

function onDragLeave() {
  document.getElementById("drop-area").classList.remove("drag-over");
}

function onDrop(event) {
  event.preventDefault();
  onDragLeave();
  if (event.dataTransfer.files[0]) {
    setFile(event.dataTransfer.files[0]);
  }
}

function onFileSelect(event) {
  if (event.target.files[0]) {
    setFile(event.target.files[0]);
  }
}

function setFile(file) {
  _selectedFile = file;
  document.getElementById("preview-name").textContent = file.name;
  document.getElementById("preview-size").textContent = formatSize(file.size);
  document.getElementById("upload-preview").style.display = "flex";
  document.getElementById("btn-upload").disabled = false;
}

function clearFile() {
  _selectedFile = null;
  document.getElementById("file-input").value = "";
  document.getElementById("upload-preview").style.display = "none";
  document.getElementById("btn-upload").disabled = true;
}

async function uploadFile() {
  if (!_selectedFile) {
    return;
  }

  const button = document.getElementById("btn-upload");
  const resultBox = document.getElementById("upload-result");
  button.disabled = true;
  button.textContent = "Se incarca...";

  const formData = new FormData();
  formData.append("file", _selectedFile, _selectedFile.name);

  try {
    const response = await fetch("/api/upload", {
      method: "POST",
      headers: { Authorization: `Bearer ${_token}` },
      body: formData,
    });
    const data = await response.json();
    resultBox.style.display = "block";

    if (data.error) {
      resultBox.className = "upload-result error";
      resultBox.textContent = `Eroare: ${data.error}`;
      return;
    }

    let message = "Adeverinta a fost incarcata cu succes.";
    if (data.data_prezenta) {
      message += ` Data detectata: ${data.data_prezenta}.`;
    }
    if (data.ore_text) {
      message += ` Ore: ${data.ore_text}.`;
    }

    resultBox.className = "upload-result success";
    resultBox.textContent = message;
    clearFile();
    loadMyAdeverinte(1);
    toast("Adeverinta incarcata.", "success");
  } catch (error) {
    resultBox.className = "upload-result error";
    resultBox.textContent = "Eroare de conexiune.";
    resultBox.style.display = "block";
  } finally {
    button.disabled = false;
    button.textContent = "Incarca adeverinta";
  }
}

async function loadMyAdeverinte(page = 1) {
  _myAdvPage = page;
  const listBox = document.getElementById("my-adv-list");
  listBox.innerHTML = '<div class="loading-row"><span class="loader"></span></div>';

  try {
    const response = await api("GET", `/api/adeverinte/my?page=${page}`);
    renderMyAdv(response.items || []);
    renderPagination("my-adv-pagination", response.pagination, "loadMyAdeverinte");
  } catch (error) {
    listBox.innerHTML = '<div class="empty">Eroare la incarcare.</div>';
  }
}

function renderMyAdv(items) {
  const listBox = document.getElementById("my-adv-list");
  const statusLabels = {
    in_asteptare: "In asteptare",
    confirmata: "Confirmata",
    respinsa: "Respinsa",
  };

  if (!items.length) {
    listBox.innerHTML = '<div class="empty">Nu aveti adeverinte incarcate inca.</div>';
    return;
  }

  listBox.innerHTML = `<div class="adv-grid">${items.map((item) => `
    <div class="adv-card">
      <div>
        <div class="adv-filename">${escapeHtml(item.filename)}</div>
        <div class="adv-meta">
          ${item.data_prezenta ? `<span>Data: ${item.data_prezenta}</span>` : ""}
          ${item.ore_text ? `<span>Ore: ${item.ore_text}</span>` : ""}
          <span>Incarcat: ${formatDate(item.uploaded_at)}</span>
        </div>
        ${item.observatii ? `<div class="adv-obs">Observatii: ${escapeHtml(item.observatii)}</div>` : ""}
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;">
        <span class="status-badge status-${item.status}">${statusLabels[item.status]}</span>
        ${item.status === "in_asteptare" ? `<button class="btn-delete" onclick="deleteAdv(${item.id})">Sterge</button>` : ""}
      </div>
    </div>
  `).join("")}</div>`;
}

async function deleteAdv(id) {
  if (!confirm("Sigur doriti sa stergeti aceasta adeverinta?")) {
    return;
  }

  try {
    const response = await api("DELETE", `/api/adeverinte/${id}`);
    if (response.error) {
      return toast(response.error, "error");
    }
    toast("Adeverinta stearsa.", "info");
    loadMyAdeverinte(_myAdvPage);
  } catch (error) {
    toast("Eroare.", "error");
  }
}

function secTab(tab) {
  document.querySelectorAll(".nav-tab").forEach((button, index) => {
    button.classList.toggle("active", (index === 0 && tab === "students") || (index === 1 && tab === "all"));
  });
  document.getElementById("tab-students").style.display = tab === "students" ? "" : "none";
  document.getElementById("tab-all").style.display = tab === "all" ? "" : "none";
}

async function loadStats() {
  try {
    const stats = await api("GET", "/api/sec/stats");
    document.getElementById("st-students").textContent = stats.students;
    document.getElementById("st-pending").textContent = stats.pending;
    document.getElementById("st-confirmed").textContent = stats.confirmed;
    document.getElementById("st-rejected").textContent = stats.rejected;
  } catch (error) {
  }
}

async function loadStudents() {
  return loadStudentsPage(1);
}

async function loadStudentsPage(page = 1) {
  _studentsListPage = page;
  const query = document.getElementById("search-student").value.trim();
  const grupa = document.getElementById("filter-grupa").value;
  const params = new URLSearchParams();
  if (query) {
    params.set("q", query);
  }
  if (grupa) {
    params.set("grupa", grupa);
  }
  params.set("page", page);

  try {
    const response = await api("GET", `/api/sec/students?${params.toString()}`);
    _allStudents = response.items || [];
    renderStudents(_allStudents);
    renderPagination("student-list-pagination", response.pagination, "loadStudentsPage");
  } catch (error) {
  }
}

function renderStudents(items) {
  const listBox = document.getElementById("student-list");
  if (!items.length) {
    listBox.innerHTML = '<div class="empty">Niciun student gasit.</div>';
    return;
  }

  listBox.innerHTML = items.map((item) => `
    <div class="student-item ${item.id === _selectedStudentId ? "active" : ""}" onclick='selectStudent(${item.id}, ${toJsString(item.nume)}, 1)'>
      <div>
        <div class="student-name">${escapeHtml(item.nume)}</div>
        <div class="student-info">${escapeHtml(item.username)} - ${escapeHtml(item.grupa || "-")} - ${item.total_adv} adeverinte</div>
      </div>
      ${item.pending > 0 ? `<div class="pending-dot" title="${item.pending} in asteptare"></div>` : ""}
    </div>
  `).join("");
}

async function selectStudent(id, name, page = 1) {
  _selectedStudentId = id;
  _selectedStudentName = name;
  _studentAdvPage = page;
  renderStudents(_allStudents);

  document.getElementById("panel-header").innerHTML = `<span class="card-title">${escapeHtml(name)}</span>`;
  document.getElementById("panel-body").innerHTML = '<div class="loading-row"><span class="loader"></span></div>';

  try {
    const response = await api("GET", `/api/sec/students/${id}/adeverinte?page=${page}`);
    renderAdvPanel(response.items || []);
    renderPagination("student-adv-pagination", response.pagination, "setStudentAdvPage");
  } catch (error) {
    document.getElementById("panel-body").innerHTML = '<div class="empty">Eroare la incarcare.</div>';
  }
}

function setStudentAdvPage(page) {
  if (_selectedStudentId) {
    selectStudent(_selectedStudentId, _selectedStudentName, page);
  }
}

function renderAdvPanel(items) {
  const panelBody = document.getElementById("panel-body");
  const statusLabels = {
    in_asteptare: "In asteptare",
    confirmata: "Confirmata",
    respinsa: "Respinsa",
  };

  if (!items.length) {
    panelBody.innerHTML = '<div class="panel-empty"><div class="panel-empty-icon">Info</div>Nicio adeverinta trimisa.</div>';
    return;
  }

  panelBody.innerHTML = items.map((item) => `
    <div class="adv-row" id="adv-row-${item.id}">
      <div class="adv-row-head">
        <div>
          <div style="font-weight:600;font-size:.9rem;margin-bottom:3px;">${escapeHtml(item.filename)}</div>
          <div style="font-size:.8rem;color:var(--ink2);">${formatDate(item.uploaded_at)}</div>
        </div>
        <span class="status-badge status-${item.status}">${statusLabels[item.status]}</span>
        <button onclick="toggleAdvRow(${item.id})" style="background:none;border:none;color:var(--ink2);cursor:pointer;font-size:1rem;padding:4px 8px;">v</button>
      </div>
      <div class="adv-row-body" id="adv-body-${item.id}">
        <div class="adv-row-body-inner">
          <div class="detail-row"><div class="detail-lbl">Data prezenta</div><div class="detail-val">${item.data_prezenta || "-"}</div></div>
          <div class="detail-row"><div class="detail-lbl">Ore</div><div class="detail-val">${item.ore_text || "-"}</div></div>
          ${item.observatii ? `<div class="detail-row" style="grid-column:1/-1"><div class="detail-lbl">Observatii</div><div class="detail-val">${escapeHtml(item.observatii)}</div></div>` : ""}
          ${item.reviewed_at ? `<div class="detail-row"><div class="detail-lbl">Revizuit la</div><div class="detail-val">${formatDate(item.reviewed_at)}</div></div>` : ""}
          ${item.reviewed_by_nume ? `<div class="detail-row"><div class="detail-lbl">Revizuit de</div><div class="detail-val">${escapeHtml(item.reviewed_by_nume)}</div></div>` : ""}
        </div>
        <div class="action-row">
          ${item.status === "in_asteptare" ? `<button class="btn-confirm" onclick='openReview(${item.id}, ${toJsString(item.filename)})'>Proceseaza</button>` : ""}
          <a class="btn-dl" href="/api/download/${item.id}?token=${encodeURIComponent(_token)}" target="_blank">Descarca</a>
        </div>
      </div>
    </div>
  `).join("");
}

function toggleAdvRow(id) {
  document.getElementById(`adv-body-${id}`).classList.toggle("open");
}

async function loadAllAdeverinte(page = 1) {
  _allAdvPage = page;
  const status = document.getElementById("filter-status-all").value;
  const search = document.getElementById("search-all").value.trim();
  const params = new URLSearchParams();
  if (status) {
    params.set("status", status);
  }
  if (search) {
    params.set("q", search);
  }
  params.set("page", page);

  const tbody = document.getElementById("all-table-body");
  tbody.innerHTML = '<tr><td colspan="9" class="loading-row"><span class="loader"></span></td></tr>';

  try {
    const response = await api("GET", `/api/sec/adeverinte?${params.toString()}`);
    renderAllTable(response.items || []);
    renderPagination("all-adv-pagination", response.pagination, "loadAllAdeverinte");
  } catch (error) {
    tbody.innerHTML = '<tr><td colspan="9" class="empty">Eroare la incarcare.</td></tr>';
  }
}

function renderAllTable(items) {
  const tbody = document.getElementById("all-table-body");
  const statusLabels = {
    in_asteptare: "In asteptare",
    confirmata: "Confirmata",
    respinsa: "Respinsa",
  };

  if (!items.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="empty">Nicio adeverinta gasita.</td></tr>';
    return;
  }

  const startIndex = (_allAdvPage - 1) * 10;
  tbody.innerHTML = items.map((item, index) => `
    <tr>
      <td style="color:var(--ink3);font-size:.8rem;">${startIndex + index + 1}</td>
      <td><b>${escapeHtml(item.student_nume)}</b><br /><span style="font-size:.78rem;color:var(--ink2);">${escapeHtml(item.username)}</span></td>
      <td>${escapeHtml(item.grupa || "-")}</td>
      <td style="font-size:.82rem;max-width:160px;word-break:break-all;">${escapeHtml(item.filename)}</td>
      <td>${item.data_prezenta || "-"}</td>
      <td>${item.ore_text || "-"}</td>
      <td><span class="status-badge status-${item.status}">${statusLabels[item.status]}</span></td>
      <td style="font-size:.8rem;color:var(--ink2);">${formatDate(item.uploaded_at)}</td>
      <td>
        <div class="table-actions">
          ${item.status === "in_asteptare" ? `<button class="btn-confirm table-action-btn" onclick='openReview(${item.id}, ${toJsString(item.filename)})'>Proceseaza</button>` : ""}
          <a class="btn-dl table-action-btn" href="/api/download/${item.id}?token=${encodeURIComponent(_token)}" target="_blank">Descarca</a>
        </div>
      </td>
    </tr>
  `).join("");
}

function openReview(advId, filename) {
  _reviewAdvId = advId;
  document.getElementById("modal-title").textContent = "Procesare adeverinta";
  document.getElementById("modal-adv-info").textContent = filename || "";
  document.getElementById("modal-obs").value = "";
  document.getElementById("modal-overlay").classList.add("open");
}

function closeModal() {
  document.getElementById("modal-overlay").classList.remove("open");
  _reviewAdvId = null;
}

async function submitReview(status) {
  if (!_reviewAdvId) {
    return;
  }

  const observatii = document.getElementById("modal-obs").value.trim();
  try {
    const response = await apiRaw("POST", `/api/adeverinte/${_reviewAdvId}/review`, {
      status,
      observatii,
    });

    if (response.error) {
      return toast(response.error, "error");
    }

    toast(status === "confirmata" ? "Adeverinta confirmata." : "Adeverinta respinsa.", status === "confirmata" ? "success" : "error");
    closeModal();
    loadStats();

    if (_selectedStudentId) {
      selectStudent(_selectedStudentId, _selectedStudentName, _studentAdvPage);
    }
    loadAllAdeverinte(_allAdvPage);
  } catch (error) {
    toast("Eroare.", "error");
  }
}

document.getElementById("modal-overlay").addEventListener("click", (event) => {
  if (event.target === document.getElementById("modal-overlay")) {
    closeModal();
  }
});

async function loadGrupe() {
  try {
    const grupe = await api("GET", "/api/sec/grupuri");
    const select = document.getElementById("filter-grupa");
    select.innerHTML = '<option value="">Toate grupele</option>';
    grupe.forEach((grupa) => {
      const option = document.createElement("option");
      option.value = grupa;
      option.textContent = grupa;
      select.appendChild(option);
    });
  } catch (error) {
  }
}

function renderPagination(hostId, pagination, actionName) {
  const host = document.getElementById(hostId);
  if (!host) {
    return;
  }

  if (!pagination || pagination.total_pages <= 1) {
    host.innerHTML = "";
    return;
  }

  const pageButtons = [];
  for (let page = 1; page <= pagination.total_pages; page += 1) {
    pageButtons.push(`
      <button class="pager-btn ${page === pagination.page ? "active" : ""}" ${page === pagination.page ? "disabled" : ""} onclick="${actionName}(${page})">${page}</button>
    `);
  }

  host.innerHTML = `
    <div class="pagination-info">Pagina ${pagination.page} din ${pagination.total_pages} - ${pagination.total_items} rezultate</div>
    <div class="pagination-controls">
      <button class="pager-btn" ${pagination.has_prev ? "" : "disabled"} onclick="${actionName}(${pagination.page - 1})">Anterior</button>
      ${pageButtons.join("")}
      <button class="pager-btn" ${pagination.has_next ? "" : "disabled"} onclick="${actionName}(${pagination.page + 1})">Urmator</button>
    </div>
  `;
}

function showPage(name) {
  document.querySelectorAll(".page").forEach((page) => page.classList.remove("active"));
  document.getElementById(`page-${name}`).classList.add("active");
}

async function api(method, url) {
  const response = await fetch(url, {
    method,
    headers: { Authorization: `Bearer ${_token}` },
  });
  return response.json();
}

async function apiRaw(method, url, body) {
  const response = await fetch(url, {
    method,
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${_token}`,
    },
    body: body ? JSON.stringify(body) : undefined,
  });
  return response.json();
}

function escapeHtml(value) {
  if (value === null || value === undefined) {
    return "";
  }

  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

function formatDate(value) {
  if (!value) {
    return "-";
  }

  try {
    const date = new Date(value.replace(" ", "T"));
    return date.toLocaleString("ro-RO", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  } catch (error) {
    return value;
  }
}

function formatSize(bytes) {
  if (bytes < 1024) {
    return `${bytes} B`;
  }
  if (bytes < 1024 * 1024) {
    return `${(bytes / 1024).toFixed(1)} KB`;
  }
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

function toast(message, type = "info") {
  const wrap = document.getElementById("toast-wrap");
  const element = document.createElement("div");
  element.className = `toast ${type}`;
  element.textContent = message;
  wrap.appendChild(element);
  setTimeout(() => element.remove(), 3500);
}

function toJsString(value) {
  return JSON.stringify(String(value || ""));
}
