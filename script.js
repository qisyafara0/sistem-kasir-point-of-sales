// FUNCTION UTNUK TOGGLESIDEBAR
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    sidebar.classList.toggle('collapsed');
    content.classList.toggle('expanded');
}

// FUNCTION SEARCH PRODUK BY KODE PRODUK
function searchByKode() {
    let kode = document.getElementById("kode_produk").value;
    if (kode.length < 2) return;

    fetch("../proses/transaksi/proses_get_produk_by_kode.php?kode_produk=" + kode)
        .then(res => res.json())
        .then(data => {
            if (!data.found) return;

            document.getElementById("nama_produk").value = data.nama_produk;
            document.getElementById("harga_produk").value = data.harga_produk;
        });
}

// FUNCTION SEARCH PRODUK BY NAME PRODUK
function searchNamaProduk() {
    let keyword = document.getElementById("nama_produk").value;
    let dropdown = document.getElementById("dropdown_produk");
    if (keyword.length < 2) {
        dropdown.innerHTML = '';
        return;
    }
    fetch("../proses/transaksi/proses_get_produk_by_name.php?keyword=" + encodeURIComponent(keyword))
        .then(res => res.json())
        .then(data => {
            dropdown.innerHTML = '';
            data.forEach(item => {
                let el = document.createElement("button");
                el.type = "button";
                el.className = "list-group-item list-group-item-action";
                el.textContent = item.nama_produk;
                el.onclick = () => selectProduk(item);

                dropdown.appendChild(el);
            });
        });
}
function selectProduk(item) {
    document.getElementById("kode_produk").value = item.kode_produk;
    document.getElementById("nama_produk").value = item.nama_produk;
    document.getElementById("harga_produk").value = item.harga_produk;

    document.getElementById("dropdown_produk").innerHTML = '';
}
document.addEventListener('click', function (e) {
    if (
        !e.target.closest('#nama_produk') &&
        !e.target.closest('#dropdown_produk')
    ) {
        document.getElementById('dropdown_produk').innerHTML = '';
    }
});

// FUNCTION UNTUK HITUNG KEMBALIAN OTOMATIS 
function hitungKembalian() {
    let total = parseInt(document.getElementById("total_belanja").value);
    let payment = parseInt(document.getElementById("tunai").value) || 0;
    let kembali = payment - total;
    if (kembali < 0) {
        document.getElementById("kembali").value = "Uang kurang";
        document.getElementById("btnCetak").disabled = true;
        return;
    }
    document.getElementById("kembali").value = "Rp " + kembali.toLocaleString("id-ID");
    document.getElementById("btnCetak").disabled = false;
    document.getElementById("payment_hidden").value = payment;
}