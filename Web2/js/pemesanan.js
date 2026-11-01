
document.addEventListener("DOMContentLoaded", function () {
    const bookingForm = document.getElementById("bookingForm");

    if (!bookingForm) return;

    const fullNameInput = document.getElementById("fullName");
    const whatsappInput = document.getElementById("whatsapp");
    const visitDateInput = document.getElementById("visitDate");
    const ticketQtyInput = document.getElementById("ticketQty");
    const totalPriceElement = document.getElementById("totalPrice");
    const ticketPriceNote = document.getElementById("ticketPriceNote");

    const decreaseQtyButton = document.getElementById("decreaseQty");
    const increaseQtyButton = document.getElementById("increaseQty");

    const submitButton = bookingForm.querySelector(
        'button[type="submit"], input[type="submit"]'
    );

    const destinationKey = document.body.dataset.destination;

    if (!destinationKey) {
        alert("Destinasi pemesanan tidak valid.");
        if (submitButton) submitButton.disabled = true;
        return;
    }

    const currencyFormatter = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    let ticketPrice = Number(document.body.dataset.ticketPrice);

    if (!Number.isFinite(ticketPrice) || ticketPrice < 0) {
        ticketPrice = 0;
    }

    function updateTotalPrice() {
        let quantity = parseInt(ticketQtyInput.value, 10);

        if (Number.isNaN(quantity)) quantity = 1;

        quantity = Math.max(1, Math.min(100, quantity));
        ticketQtyInput.value = quantity;

        const total = quantity * ticketPrice;

        if (totalPriceElement) {
            totalPriceElement.textContent =
                currencyFormatter.format(total);
        }

        if (ticketPriceNote) {
            ticketPriceNote.textContent = ticketPrice === 0
                ? "Destinasi ini gratis. Tidak ada biaya tiket."
                : "Harga tiket: " +
                  currencyFormatter.format(ticketPrice) +
                  " per tiket";
        }
    }

    if (decreaseQtyButton) {
        decreaseQtyButton.addEventListener("click", function () {
            const quantity = parseInt(ticketQtyInput.value, 10) || 1;

            if (quantity > 1) {
                ticketQtyInput.value = quantity - 1;
                updateTotalPrice();
            }
        });
    }

    if (increaseQtyButton) {
        increaseQtyButton.addEventListener("click", function () {
            const quantity = parseInt(ticketQtyInput.value, 10) || 1;

            if (quantity < 100) {
                ticketQtyInput.value = quantity + 1;
                updateTotalPrice();
            }
        });
    }

    ticketQtyInput.addEventListener("input", updateTotalPrice);
    ticketQtyInput.addEventListener("change", updateTotalPrice);

    if (visitDateInput) {
        const today = new Date();

        visitDateInput.min = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, "0"),
            String(today.getDate()).padStart(2, "0")
        ].join("-");
    }

    updateTotalPrice();

    bookingForm.addEventListener("submit", async function (event) {
        event.preventDefault();

        const name = fullNameInput.value.trim();
        const phone = whatsappInput.value.trim();
        const date = visitDateInput.value;
        const quantity = Number(ticketQtyInput.value);

        if (!name) {
            alert("Nama lengkap wajib diisi.");
            fullNameInput.focus();
            return;
        }

        const digitCount = phone.replace(/\D/g, "").length;

        if (!/^[0-9+\-\s]{9,30}$/.test(phone) || digitCount < 9) {
            alert("Masukkan nomor WhatsApp yang valid.");
            whatsappInput.focus();
            return;
        }

        if (!date || (visitDateInput.min && date < visitDateInput.min)) {
            alert("Pilih tanggal kunjungan yang valid.");
            visitDateInput.focus();
            return;
        }

        if (
            !Number.isInteger(quantity) ||
            quantity < 1 ||
            quantity > 100
        ) {
            alert("Jumlah tiket harus antara 1 sampai 100.");
            ticketQtyInput.focus();
            return;
        }

        const originalButtonText = submitButton
            ? submitButton.textContent
            : "";

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = "Memproses...";
        }

        try {
            const payload = {
                destination: destinationKey,
                name: name,
                whatsapp: phone,
                date: date,
                quantity: quantity
            };

            // Simpan pesanan ke folder utama Web2
            const response = await fetch("../simpan.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "same-origin",
                body: JSON.stringify(payload)
            });

            // Periksa respons sebelum mengubahnya menjadi JSON
            const responseText = await response.text();

            let result;

            try {
                result = JSON.parse(responseText);
            } catch (parseError) {
                console.error("Respons server bukan JSON:", responseText);

                throw new Error(
                    "Server tidak mengirim respons JSON. Periksa file simpan.php atau alamat pemanggilannya."
                );
            }

            if (response.status === 401) {
                alert("Silakan masuk terlebih dahulu untuk memesan.");
                window.location.href = "../masuk.php";
                return;
            }

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Pemesanan gagal disimpan."
                );
            }

            if (!result.booking_code) {
                throw new Error(
                    "Kode booking tidak diterima dari server."
                );
            }

            // Untuk alur ini, hanya destinasi berbayar yang diproses.
            if (Number(result.total_price) <= 0) {
                throw new Error(
                    "Harga tiket belum tersedia atau destinasi ini tidak berbayar."
                );
            }

            // Arahkan ke halaman pembayaran QRIS
            window.location.href =
                "../pembayaran.php?booking=" +
                encodeURIComponent(result.booking_code);

        } catch (error) {
            console.error("Kesalahan pemesanan:", error);

            alert(
                error.message ||
                "Terjadi kesalahan saat memproses pesanan."
            );

        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        }
    });
});
