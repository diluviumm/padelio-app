@extends('layouts.app')
@section('title', 'Ketentuan Layanan')

@section('content')
<div class="page-head">
    <h1>Ketentuan Layanan</h1>
    <p>Aturan komunitas dan penggunaan Padelio.</p>
</div>

<div class="card pad" style="max-width:820px">
    <b class="sec-title" style="margin-top:0">Akun</b>
    <p class="small muted">Satu akun untuk satu pemain. Kamu bertanggung jawab menjaga password dan aktivitas di akunmu. Jangan membuat akun palsu, meniru pemain lain, atau menyalahgunakan panel admin.</p>

    <b class="sec-title">Pertandingan dan ELO</b>
    <p class="small muted">Skor diinput setelah bertanding, dikonfirmasi kedua pemain, lalu disetujui admin. Hasil yang disetujui mengubah rating dan tidak dibatalkan sepihak. Sengketa skor diselesaikan oleh admin berdasarkan bukti.</p>

    <b class="sec-title">Booking</b>
    <p class="small muted">Slot yang sudah dibooking tidak dapat dibooking ulang. Pembatalan mengembalikan stok peralatan. Pembayaran pada versi ini masih dicatat manual (mock), belum melalui gateway; harga selalu dihitung di server.</p>

    <b class="sec-title">Perilaku</b>
    <p class="small muted">Dilarang menyebarkan konten ilegal, melakukan serangan teknis, mencoba mengakses akun lain, atau mengganggu layanan. Pelanggaran dapat menyebabkan suspensi akun dan penghapusan data terkait.</p>

    <b class="sec-title">Batas tanggung jawab</b>
    <p class="small muted">Aplikasi disediakan apa adanya untuk komunitas dan demo. Pengelola tidak menjamin ketersediaan tanpa henti dan tidak bertanggung jawab atas cedera atau kerugian saat bermain di lapangan pihak ketiga.</p>

    <p class="faint small" style="margin-top:16px">Terakhir diperbarui: 29 Oktober 2026. Dengan memakai Padelio, kamu menyetujui ketentuan ini.</p>
</div>
@endsection
