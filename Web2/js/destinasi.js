/* =========================================================
   DATA DESTINASI WISATA CIAMIS
========================================================= */

const destinations = [
  {
    id: 1,
    desa: "Kawali",
    kecamatan: "Kawali",
    nama: "Astana Gede Kawali",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/AstanaGedeKawali.jpeg",
    detail: "detail/kawali.php",
  },

  {
    id: 2,
    desa: "Winduraja",
    kecamatan: "Kawali",
    nama: "Situ Wangi",
    kategori: "Alam",
    gambar: "pictures/destinasi/WinduRaja.jpeg",
    detail: "detail/situwangi.php",

  },

  {
    id: 3,
    desa: "Talagasari",
    kecamatan: "Kawali",
    nama: "Kampung Sawah",
    kategori: "Buatan",
    gambar: "pictures/destinasi/KampungSawah.jpeg",
    detail: "detail/talagasari.php",
  },

  {
    id: 4,
    desa: "Sidamulih",
    kecamatan: "Pamarican",
    nama: "Pasir Jati Grown Hill",
    kategori: "Alam",
    gambar: "pictures/destinasi/PasirJatiGrownHill.jpeg",
    detail: "detail/sidamulih.php",
  },

  {
    id: 5,
    desa: "Mekarsari",
    kecamatan: "Tambaksari",
    nama: "Puncak Meralaya",
    kategori: "Alam",
    gambar: "pictures/destinasi/PuncakMaralaya.jpeg",
    detail: "detail/mekarsari.php",
  },

  {
    id: 6,
    desa: "Ciklar",
    kecamatan: "Cipaku",
    nama: "Wisata Murbey",
    kategori: "Buatan", 
    gambar: "pictures/destinasi/Cipaku.jpeg",
    detail: "detail/ciklar.html"
  },

  {
    id: 7,
    desa: "Cintanagara",
    kecamatan: "Jatinagara",
    nama: "Wisata Alam, Agrowisata & Jalan Usaha Tani",
    kategori: "Alam",
    gambar: "pictures/destinasi/CintaNagara.webp",
    detail: "detail/cintanagara.html",
  },

  {
    id: 8,
    desa: "Karangkamulyan",
    kecamatan: "Cijeungjing",
    nama: "Situs Karangkamulyan",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/Karangkamulyan.jpeg",
    detail: "detail/karangkamulyan.html",
  },

  {
    id: 9,
    desa: "Bangunharja",
    kecamatan: "Cisaga",
    nama: "Puncak Manik",
    kategori: "Alam",
    gambar: "pictures/destinasi/PuncakManik.jpeg",
    deatail: "detail/bangunharja.html",
  },

  {
    id: 10,
    desa: "Cisontrol",
    kecamatan: "Rancah",
    nama: "Wisata Edukasi Anak",
    kategori: "Buatan",
    gambar: "pictures/destinasi/WisataEdukasiAnakCisontol.jpeg",
    detail: "detail/cisontrol.html",
  },

  {
    id: 11,
    desa: "Situmandala",
    kecamatan: "Rancah",
    nama: "Puncak Bangku & Puncak Pispun",
    kategori: "Alam",
    gambar: "pictures/destinasi/PuncakBangku.jpeg",
    detail: "detail/situmandala.html",
  },

  {
    id: 12,
    desa: "Wanasigra",
    kecamatan: "Sindangkasih",
    nama: "Situs Gandoang",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/Gandongan.webp",
    detail: "detail/wanasigra.html",
  },

  {
    id: 13,
    desa: "Sukahurip",
    kecamatan: "Cihaurbeuti",
    nama: "Curug Salosin",
    kategori: "Alam",
    gambar: "pictures/destinasi/CurugSalosin.jpeg",
    detail: "detail/sukahurip.html",
  },

  {
    id: 14,
    desa: "Sumberjaya",
    kecamatan: "Cihaurbeuti",
    nama: "Taman Wisata & Bumi Perkemahan",
    kategori: "Buatan",
    gambar: "pictures/destinasi/BumiPekemahan.jpeg",
    detail: "detail/sumberjaya.html",
  },

  {
    id: 15,
    desa: "Aomas",
    kecamatan: "Panjalu",
    nama: "Paralayang",
    kategori: "Alam",
    gambar: "pictures/destinasi/ParalayangBukitPangangonan.jpeg",
    detail: "detail/aomas.html",
  },

  {
    id: 16,
    desa: "Panjalu",
    kecamatan: "Panjalu",
    nama: "Situ Lengkong Panjalu",
    kategori: "Alam",
    gambar: "pictures/destinasi/SituLengkongPanjalu.jpeg",
    detail: "detail/panjalu.html",
  },

  {
    id: 17,
    desa: "Cikupa",
    kecamatan: "Banjarsari",
    nama: "Pemandian Air Panas",
    kategori: "Buatan",
    gambar: "pictures/destinasi/PemandianAirPanas.webp",
    detail: "detail/cikupa.html",
  },

  {
    id: 18,
    desa: "Banjaranyar",
    kecamatan: "Banjaranyar",
    nama: "Kampung Madu",
    kategori: "Buatan",
    gambar: "pictures/destinasi/KampungMadu.jpeg",
    detail: "detail/banjayanyar.html", 
  },

  {
    id: 19,
    desa: "Karangpaningal",
    kecamatan: "Tambaksari",
    nama: "Kampung Kuta",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/KampungKuta.jpeg",
    detail: "detail/karangpaningal.html",
  },

  {
    id: 20,
    desa: "Baregbeg",
    kecamatan: "Lakbok",
    nama: "Layang Lakbok",
    kategori: "Buatan",
    gambar: "pictures/destinasi/LayangLakbok.jpeg",
    detail: "detail/baregbeg.html",
  },

  {
    id: 21,
    desa: "Cibeureum",
    kecamatan: "Sukamantri",
    nama: "Situ Cibubuhan",
    kategori: "Alam",
    gambar: "pictures/destinasi/SituCibubuhan.webp",
    detail: "detail/cibereum.html",
  },

  {
    id: 22,
    desa: "Gunungsari",
    kecamatan: "Sadananya",
    nama: "Cadas Ngampar",
    kategori: "Alam",
    gambar: "pictures/destinasi/CadasNgampar.webp",
    detail: "detail/gunungsari.html",
  },

  {
    id: 23,
    desa: "Kawasen",
    kecamatan: "Banjarsari",
    nama: "Tondong Golok",
    kategori: "Alam",
    gambar: "pictures/destinasi/TonjongGolok.jpeg",
    detail: "detail/kawasen.html",
  },

  {
    id: 24,
    desa: "Sagalaherang",
    kecamatan: "Panawangan",
    nama: "Paralayang",
    kategori: "Alam",
    gambar: "pictures/destinasi/ParalayangPanawangan.webp",
    detail: "detail/sagalaherang.html",
  },

  {
    id: 25,
    desa: "Kertajaya",
    kecamatan: "Panawangan",
    nama: "Puncak Kertajaya",
    kategori: "Alam",
    gambar: "pictures/destinasi/PuncakKartajaya.jpeg",
    detail: "detail/kertajaya.html",
  },

  {
    id: 26,
    desa: "Raksabaya",
    kecamatan: "Cimaragas",
    nama: "Kampung Teratai",
    kategori: "Buatan",
    gambar: "pictures/destinasi/KampungTeratai.jpeg",
    detail: "detail/raksabaya.html",
  },

  {
    id: 27,
    desa: "Pusakasari",
    kecamatan: "Cipaku",
    nama: "Agro Wisata",
    kategori: "Buatan",
    gambar: "pictures/destinasi/AgroCipaku.webp",
    detail: "detail/pukasari.html",
  },

  {
    id: 28,
    desa: "Sadeweata",
    kecamatan: "Lumbung",
    nama: "Situ Hujang",
    kategori: "Alam",
    gambar: "pictures/destinasi/SiituHiang.jpeg",
    detail: "detail/sadeweata.html",
  },

  {
    id: 29,
    desa: "Cimari",
    kecamatan: "Cikoneng",
    nama: "Situ Rancabala",
    kategori: "Alam",
    gambar: "pictures/destinasi/SituRancabala.jpeg",
    detail: "detail/cimari.html",
  },

  {
    id: 30,
    desa: "Sukajaya",
    kecamatan: "Rajadesa",
    nama: "Puncak Upsa Rahong",
    kategori: "Alam",
    gambar: "pictures/destinasi/PuncakUpsa.jpeg",
    detail: "detail/sukajaya.html",
  },

  {
    id: 31,
    desa: "Jalatrang",
    kecamatan: "Cipaku",
    nama: "Kampung Bungur",
    kategori: "Buatan",
    gambar: "pictures/destinasi/KampungBangur.jpeg",
    detail: "detail/jalatrang.html",
  },

  {
    id: 32,
    desa: "Rajadesa",
    kecamatan: "Rajadesa",
    nama: "Samida & Situs Gunung Marapi",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/BukitSamida.webp",
    detail: "detail/rajadesa.html",
  },

  {
    id: 33,
    desa: "Sukamaju",
    kecamatan: "Cihaurbeuti",
    nama: "Bukit Sempalan Asri & Lobang Timah",
    kategori: "Alam",
    gambar: "pictures/destinasi/BukitSemparanAsli.jpeg",
    detail: "detail/sukamaju.html",
  },

  {
    id: 34,
    desa: "Selamanik",
    kecamatan: "Cipaku",
    nama: "Homestay & Agrowisata",
    kategori: "Buatan",
    gambar: "pictures/destinasi/selamanik.jpeg",
    detail: "detail/selamanik.html",
  },

  {
    id: 35,
    desa: "Sukaresik",
    kecamatan: "Sindangkasih",
    nama: "Sungai Cireong & Situs Gunung Padang",
    kategori: "Alam",
    gambar: "pictures/destinasi/SungaiCireong.jpeg",
    detail: "detail/sukaresik.html",
  },

  {
    id: 36,
    desa: "Darmacaang",
    kecamatan: "Cikoneng",
    nama: "Hutan Pinus Batu Cakra",
    kategori: "Alam",
    gambar: "pictures/destinasi/PinusBatuCakra.jpeg",
    detail: "detail/darmacaang.html",
  },

  {
    id: 37,
    desa: "Kaso",
    kecamatan: "Tambaksari",
    nama: "Embung Ciawade & Seni Budaya Mekar Arum",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/Ciawade.jpeg",
    detail: "detail/kaso.html",
  },

  {
    id: 38,
    desa: "Bahara",
    kecamatan: "Panjalu",
    nama: "Situs Cipanjalu",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/Cipanjalu.jpeg",
    detail: "detail/bahara.html",
  },

  {
    id: 39,
    desa: "Sukadana",
    kecamatan: "Sukadana",
    nama: "Agrowisata & Cagar Budaya Rancah Bintang",
    kategori: "Buatan",
    gambar: "pictures/destinasi/RumahBintang.jpeg",
    detail: "detail/sukadana.html",
  },

  {
    id: 40,
    desa: "Cibadak",
    kecamatan: "Banjarsari",
    nama: "Agrowisata, Buper & Pangangonan",
    kategori: "Buatan",
    gambar: "pictures/destinasi/PangangonanCibadak.jpeg",
    detail: "detail/cibadak.html",
  },

  {
    id: 41,
    desa: "Nasol",
    kecamatan: "Cikoneng",
    nama: "Jaha Buana",
    kategori: "Alam",
    gambar: "pictures/destinasi/JahaBuana.jpeg",
    detail: "detail/nasol.html",
  },

  {
    id: 42,
    desa: "Gunungcupu",
    kecamatan: "Sindangkasih",
    nama: "Kampung Legenda",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/KampungLegenda.jpeg",
    detail: "detail/gunungcupu.html",
  },

  {
    id: 43,
    desa: "Kadupandak",
    kecamatan: "Tambaksari",
    nama: "Cekdam Kadupandak",
    kategori: "Buatan",
    gambar: "pictures/destinasi/CekdamKadupandak.jpeg",
    detail: "detail/kadupandak.html",
  },

  {
    id: 44,
    desa: "Sukasenang",
    kecamatan: "Sindangkasih",
    nama: "Situ Rancamaya",
    kategori: "Alam",
    gambar: "pictures/destinasi/SituRancamayana.jpeg",
    detail: "detail/sukasenang.html",
  },

  {
    id: 45,
    desa: "Jagabaya",
    kecamatan: "Panawangan",
    nama: "Bukit Culaak",
    kategori: "Alam",
    gambar: "pictures/destinasi/BukitCikulak.jpeg",
    detail: "detail/jagabaya.html",
  },

  {
    id: 46,
    desa: "Kertabumi",
    kecamatan: "Cijeungjing",
    nama: "Situs Gunung Susuru",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/GunungSusuru.jpeg",
    detail: "detail/kertabumi.html",
  },

  {
    id: 47,
    desa: "Giriharja",
    kecamatan: "Rancah",
    nama: "Jembatan Swiss, Kolam Renang & Taman Swiss",
    kategori: "Buatan",
    gambar: "pictures/destinasi/JembatanSwiss.jpeg",
    detail: "detail/giriharja.html",
  },

  {
    id: 48,
    desa: "Werasari",
    kecamatan: "Sadananya",
    nama: "Agrowisata Pasir Boma",
    kategori: "Buatan",
    gambar: "pictures/destinasi/PasirBoma.jpeg",
    detail: "detail/werasari.html",
  },

  {
    id: 49,
    desa: "Cidadap",
    kecamatan: "Ciamis",
    nama: "Wisata Religi Makam Bupati Galuh",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/MakanBupatiGaluh.jpeg",
    detail: "detail/cidadap.html",
  },

  {
    id: 50,
    desa: "Panyingkiran",
    kecamatan: "Ciamis",
    nama: " Wisata Alam Kampung Angklung",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/KampungAngklung.jpeg",
    detail: "detail/panyingkiran.html",
  },

  {
    id: 51,
    desa: "Sukawening",
    kecamatan: "Cipaku",
    nama: "Wisata Alam Cibagi",
    kategori: "Alam",
    gambar: "pictures/destinasi/Cibagi.jpeg",
    detail: "detail/sukawening.html",
  },

  {
    id: 52,
    desa: "Kertamandala",
    kecamatan: "Panjalu",
    nama: "Makam Situs Cikapunduhan & Villa Srikandi",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/VillaSrikandi.jpeg",
    detail: "detail/kertamandala.html"
  },

  {
    id: 53,
    desa: "Padamulya",
    kecamatan: "Cihaurbeuti",
    nama: "Curug Seda & Buper Puncak Asih",
    kategori: "Alam",
    gambar: "pictures/destinasi/CurugSeda.jpeg",
    detail: "detail/padamulya.html",
  },

  {
    id: 54,
    desa: "Tanjungsari",
    kecamatan: "Sadananya",
    nama: "Curug Panganten",
    kategori: "Alam",
    gambar:"pictures/destinasi/CurugPanganten.jpeg",
    detail: "detail/tanjungsari.html",
  },

  {
    id: 55,
    desa: "Sukasetia",
    kecamatan: "Cihaurbeuti",
    nama: "Desa Adat Sejahtera Berencana",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/DesaAdatSejahtera.jpeg",
    detail: "detail/sukasetia.html",
  },

  {
    id: 56,
    desa: "Maparah",
    kecamatan: "Panjalu",
    nama: "Situs Aria Dipa & Taman Uncal Bubulak",
    kategori: "Sejarah & Budaya",
    gambar: "pictures/destinasi/TamanUncalBubulak.jpeg",
    detail: "detail/maparah.html",
  },

  {
    id: 57,
    desa: "Sandingtaman",
    kecamatan: "Panjalu",
    nama: "Curug Tujuh, Buper Aki Kabayan & Situs Odehem",
    kategori: "Alam",
    gambar: "pictures/destinasi/CurugTujuhCibolang.jpeg",
    detail: "detail/sandingtaman.html",
  },
];

/* =========================================================
   ELEMENT HTML
========================================================= */

const destinationContainer = document.getElementById("destinationContainer");

const resultCount = document.getElementById("resultCount");

const emptyResult = document.getElementById("emptyResult");

/* =========================================================
   ICON KATEGORI
========================================================= */

function getCategoryIcon(category) {
  if (category === "Alam") {
    return "bi-tree-fill";
  }

  if (category === "Buatan") {
    return "bi-building";
  }

  return "bi-bank";
}

/* =========================================================
   BUAT CARD DESTINASI
========================================================= */

function createDestinationCard(destination) {
  const column = document.createElement("div");

  column.className = "col-lg-4 col-md-6";

  const detailButton = destination.detail
    ? `
            <a
                href="${destination.detail}"
                class="btn btn-primary btn-sm"
            >
                Lihat Detail
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        `
    : `
            <button
                type="button"
                class="btn btn-outline-secondary btn-sm"
                disabled
            >
                Detail Segera
            </button>
        `;

  column.innerHTML = `

        <div class="destination-card h-100">


            <div class="destination-image">

                <img
                    src="${destination.gambar}"
                    alt="${destination.nama}"
                    onerror="this.src='pictures/Logo Dispar.png'"
                >

                <span class="destination-category">

                    <i class="bi ${getCategoryIcon(destination.kategori)}"></i>

                    ${destination.kategori}

                </span>

            </div>


            <div class="destination-card-body">


                <span class="destination-location">

                    <i class="bi bi-geo-alt-fill"></i>

                    Desa ${destination.desa},
                    Kecamatan ${destination.kecamatan}

                </span>


                <h3>
                    ${destination.nama}
                </h3>


                <p>

                    Destinasi wisata ${destination.nama}
                    berada di Desa ${destination.desa},
                    Kabupaten Ciamis.

                </p>


                <div class="destination-card-footer">

                    ${detailButton}

                </div>


            </div>


        </div>

    `;

  return column;
}

/* =========================================================
   MENAMPILKAN DESTINASI
========================================================= */

function displayDestinations(data) {
  destinationContainer.innerHTML = "";

  resultCount.textContent = data.length;

  if (data.length === 0) {
    emptyResult.style.display = "block";

    return;
  }

  emptyResult.style.display = "none";

  data.forEach(function (destination) {
    const card = createDestinationCard(destination);

    destinationContainer.appendChild(card);
  });
}

/* =========================================================
   FILTER BERDASARKAN KATEGORI
========================================================= */

function filterByCategory(category) {
  if (!category || category === "Semua") {
    displayDestinations(destinations);

    return;
  }

  const filteredDestinations = destinations.filter(function (destination) {
    return destination.kategori === category;
  });

  displayDestinations(filteredDestinations);
}

/* =========================================================
   MEMBACA KATEGORI DARI URL
========================================================= */

function readCategoryFromURL() {
  const params = new URLSearchParams(window.location.search);

  const category = params.get("kategori");

  if (category === "Alam" || category === "Buatan" || category === "Sejarah & Budaya") {
    filterByCategory(category);
  } else {
    filterByCategory("Semua");
  }
}

/* =========================================================
   PROGRAM DIJALANKAN
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
  readCategoryFromURL();
});


document.addEventListener("DOMContentLoaded", function () {
  // GANTI dengan harga tiket resmi per orang
  const HARGA_TIKET = 10000;

  const form = document.getElementById("bookingForm");
  const qtyInput = document.getElementById("bookingQty");
  const dateInput = document.getElementById("bookingDate");
  const totalOutput = document.getElementById("bookingTotal");
  const paymentCard = document.getElementById("paymentCard");

  const rupiah = (angka) =>
    new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      maximumFractionDigits: 0
    }).format(angka);

  // Tanggal minimum: hari ini, menggunakan waktu lokal
  const now = new Date();
  const today = [
    now.getFullYear(),
    String(now.getMonth() + 1).padStart(2, "0"),
    String(now.getDate()).padStart(2, "0")
  ].join("-");

  dateInput.min = today;

  function getJumlah() {
    const jumlah = Number(qtyInput.value);
    return Number.isFinite(jumlah)
      ? Math.min(100, Math.max(1, Math.floor(jumlah)))
      : 1;
  }

  function updateTotal() {
    const jumlah = getJumlah();
    qtyInput.value = jumlah;
    totalOutput.textContent = rupiah(jumlah * HARGA_TIKET);
  }

  document.getElementById("qtyMinus").addEventListener("click", () => {
    qtyInput.value = getJumlah() - 1;
    updateTotal();
  });

  document.getElementById("qtyPlus").addEventListener("click", () => {
    qtyInput.value = Math.min(100, getJumlah() + 1);
    updateTotal();
  });

  qtyInput.addEventListener("change", updateTotal);
  qtyInput.addEventListener("input", updateTotal);

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    if (!form.reportValidity()) return;

    const nama = document.getElementById("bookingName").value.trim();
    const tanggal = dateInput.value;
    const jumlah = getJumlah();
    const total = jumlah * HARGA_TIKET;

    if (!nama || !tanggal || HARGA_TIKET <= 0) {
      alert("Periksa kembali data pemesanan dan tarif tiket.");
      return;
    }

    document.getElementById("paymentTotal").textContent = rupiah(total);
    document.getElementById("paymentDetail").textContent =
      `${jumlah} tiket × ${rupiah(HARGA_TIKET)} · ${tanggal}`;

    paymentCard.hidden = false;

    paymentCard.scrollIntoView({
      behavior: "smooth",
      block: "nearest"
    });
  });

  updateTotal();
});
