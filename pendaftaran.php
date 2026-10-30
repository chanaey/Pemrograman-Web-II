
<?php
// KONEKSI DATABASE
$conn = mysqli_connect("localhost", "root", "", "db_pendaftaran");

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan = "";

// PROSES SIMPAN DATA
if (isset($_POST['simpan'])) {

    $tgl_input = $_POST['tgl_input'];
    $kode = strtoupper(trim($_POST['kode']));
    $nama = trim($_POST['nama']);
    $jk = $_POST['jk'];
    $asal_sekolah = trim($_POST['asal_sekolah']);
    $pekerjaan_ortu = trim($_POST['pekerjaan_ortu']);
    $tempat_lahir = trim($_POST['tempat_lahir']);
    $tgl_lahir = $_POST['tgl_lahir'];

    $mat = (int) $_POST['mat'];
    $bing = (int) $_POST['bing'];
    $umum = (int) $_POST['umum'];

    // HITUNG RATA-RATA
    $rata = ($mat + $bing + $umum) / 3;

    // MENENTUKAN KETERANGAN
    if ($rata >= 70) {
        $keterangan = "Lulus";
    } elseif ($rata >= 60) {
        $keterangan = "Cadangan";
    } else {
        $keterangan = "Tidak Lulus";
    }

    // MENENTUKAN TEMPAT TES
    $kode_awal = substr($kode, 0, 1);

    if ($kode_awal == "A") {
        $tempat_tes = "Gedung A";
    } elseif ($kode_awal == "B") {
        $tempat_tes = "Gedung B";
    } elseif ($kode_awal == "V") {
        $tempat_tes = "Viktor";
    } else {
        $tempat_tes = "Tidak Diketahui";
    }

    // SIMPAN KE DATABASE
    $sql = "INSERT INTO mahasiswa
    (tgl_input, kode, nama, jk, asal_sekolah,
    pekerjaan_ortu, tempat_lahir, tgl_lahir,
    matematika, bahasa_inggris, umum,
    rata_rata, keterangan, tempat_tes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssiiidss",
        $tgl_input,
        $kode,
        $nama,
        $jk,
        $asal_sekolah,
        $pekerjaan_ortu,
        $tempat_lahir,
        $tgl_lahir,
        $mat,
        $bing,
        $umum,
        $rata,
        $keterangan,
        $tempat_tes
    );

    if (mysqli_stmt_execute($stmt)) {
        $pesan = "Data mahasiswa berhasil disimpan!";
    } else {
        $pesan = "Data gagal disimpan: " .
                 mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
}

// AMBIL SEMUA DATA DARI DATABASE
$query = mysqli_query(
    $conn,
    "SELECT * FROM mahasiswa ORDER BY id DESC"
);

// HITUNG REKAP DATA
$total_pendaftar = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM mahasiswa")
)['total'];

$total_lulus = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM mahasiswa
         WHERE keterangan = 'Lulus'"
    )
)['total'];

$total_cadangan = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM mahasiswa
         WHERE keterangan = 'Cadangan'"
    )
)['total'];

$total_tidak_lulus = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total FROM mahasiswa
         WHERE keterangan = 'Tidak Lulus'"
    )
)['total'];

// FUNGSI UNTUK MENGAMANKAN TAMPILAN TEKS
function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>UTS PHP - Pendaftaran Mahasiswa</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 30px;
            background: #f4f4f4;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type=text],
        input[type=number],
        input[type=date] {
            width: 100%;
            box-sizing: border-box;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .btn {
            background: blue;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn:hover {
            background: darkblue;
        }

        .info {
            margin-top: 20px;
            padding: 15px;
            background: #eef;
            border-radius: 5px;
        }

        .pesan {
            margin-top: 15px;
            padding: 12px;
            background: #d4edda;
            color: #155724;
            border-radius: 5px;
        }

        .tabel {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background: #dce6ff;
        }

        @media (max-width: 600px) {
            body {
                margin: 10px;
            }

            .container {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h2>FORM PENDAFTARAN MAHASISWA BARU</h2>

    <form method="POST">

        <label>Tanggal Input</label>
        <input type="date" name="tgl_input" required>

        <label>Kode Pendaftaran</label>
        <input type="text" name="kode"
               placeholder="Contoh: A2" required>

        <label>Nama Pendaftar</label>
        <input type="text" name="nama" required>

        <label>Jenis Kelamin</label>
        <input type="radio" name="jk"
               value="Laki-Laki" required> Laki-Laki

        <input type="radio" name="jk"
               value="Perempuan"> Perempuan

        <label>Asal Sekolah</label>
        <input type="text" name="asal_sekolah" required>

        <label>Pekerjaan Orang Tua</label>
        <input type="text" name="pekerjaan_ortu" required>

        <label>Tempat Lahir</label>
        <input type="text" name="tempat_lahir" required>

        <label>Tanggal Lahir</label>
        <input type="date" name="tgl_lahir" required>

        <label>Matematika</label>
        <input type="number" name="mat"
               min="0" max="100" required>

        <label>Bahasa Inggris</label>
        <input type="number" name="bing"
               min="0" max="100" required>

        <label>Umum</label>
        <input type="number" name="umum"
               min="0" max="100" required>

        <br>

        <button type="submit" name="simpan"
                value="1" class="btn">
            SIMPAN
        </button>

    </form>

    <?php if ($pesan != ""): ?>
        <div class="pesan">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>


    <!-- TABEL DATA MAHASISWA -->

    <h2>DATA PENDAFTARAN MAHASISWA</h2>

    <div class="tabel">
        <table>
            <tr>
                <th>No.</th>
                <th>Tanggal Input</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>JK</th>
                <th>Tempat, Tanggal Lahir</th>
                <th>Asal Sekolah</th>
                <th>Pekerjaan Ortu</th>
                <th>Tempat Tes</th>
                <th>MAT</th>
                <th>B. Inggris</th>
                <th>Umum</th>
                <th>Rata-rata</th>
                <th>Keterangan</th>
            </tr>

            <?php
            $no = 1;

            if (mysqli_num_rows($query) > 0):
                while ($data = mysqli_fetch_assoc($query)):
            ?>

            <tr>
                <td><?= $no++ ?></td>
                <td><?= aman($data['tgl_input']) ?></td>
                <td><?= aman($data['kode']) ?></td>
                <td><?= aman($data['nama']) ?></td>
                <td><?= aman($data['jk']) ?></td>

                <td>
                    <?= aman($data['tempat_lahir']) ?>,
                    <?= aman($data['tgl_lahir']) ?>
                </td>

                <td><?= aman($data['asal_sekolah']) ?></td>
                <td><?= aman($data['pekerjaan_ortu']) ?></td>
                <td><?= aman($data['tempat_tes']) ?></td>
                <td><?= aman($data['matematika']) ?></td>
                <td><?= aman($data['bahasa_inggris']) ?></td>
                <td><?= aman($data['umum']) ?></td>

                <td>
                    <?= number_format(
                        (float)$data['rata_rata'], 2
                    ) ?>
                </td>

                <td><?= aman($data['keterangan']) ?></td>
            </tr>

            <?php
                endwhile;
            else:
            ?>

            <tr>
                <td colspan="14">Belum ada data pendaftar.</td>
            </tr>

            <?php endif; ?>

        </table>
    </div>


    <!-- REKAP DATA -->

    <div class="info">

        <h3>REKAP DATA PENDAFTAR</h3>

        <p>
            Jumlah Pendaftar:
            <b><?= $total_pendaftar ?></b>
        </p>

        <p>
            Jumlah Peserta Lulus:
            <b><?= $total_lulus ?></b>
        </p>

        <p>
            Jumlah Peserta Cadangan:
            <b><?= $total_cadangan ?></b>
        </p>

        <p>
            Jumlah Peserta Tidak Lulus:
            <b><?= $total_tidak_lulus ?></b>
        </p>

    </div>


    <!-- KETERANGAN -->

    <div class="info">

        <h3>KETERANGAN</h3>

        <p><b>Kode Tempat Tes:</b></p>

        <ul>
            <li>A = Gedung A</li>
            <li>B = Gedung B</li>
            <li>V = Viktor</li>
        </ul>

        <p><b>Hasil Seleksi:</b></p>

        <ul>
            <li>Rata-rata ≥ 70 = Lulus</li>
            <li>Rata-rata ≥ 60 dan &lt; 70 = Cadangan</li>
            <li>Rata-rata &lt; 60 = Tidak Lulus</li>
        </ul>

    </div>

</div>

</body>
</html>
