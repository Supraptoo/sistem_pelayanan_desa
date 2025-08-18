<?php
include '../includes/config.php';
include '../includes/db.php';
include '../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sejarah Desa</li>
            </ol>
        </nav>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm mb-5">
                    <div class="card-body p-5">
                        <div class="text-center mb-5">
                            <h1 class="fw-bold mb-3">Sejarah Desa <?php echo $nama_desa; ?></h1>
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="icon-xl bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                                    <i class="bi bi-book"></i>
                                </div>
                                <h3 class="mb-0">Didirikan Tahun <?php echo $tahun_berdiri; ?></h3>
                            </div>
                        </div>
                        
                        <div class="content mb-5">
                            <?php echo nl2br($sejarah_desa); ?>
                        </div>
                        
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4">
                                        <h4 class="mb-4">Visi Desa</h4>
                                        <p>"Mewujudkan Desa <?php echo $nama_desa; ?> yang maju, mandiri, dan sejahtera melalui pemberdayaan masyarakat berbasis potensi lokal dengan prinsip gotong royong."</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body p-4">
                                        <h4 class="mb-4">Misi Desa</h4>
                                        <ol>
                                            <li class="mb-2">Meningkatkan kualitas sumber daya manusia</li>
                                            <li class="mb-2">Mengembangkan potensi ekonomi lokal</li>
                                            <li class="mb-2">Memperkuat infrastruktur dasar</li>
                                            <li class="mb-2">Meningkatkan pelayanan publik</li>
                                            <li>Melestarikan budaya dan kearifan lokal</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h3 class="mb-4">Perkembangan Desa</h3>
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-year"><?php echo $tahun_berdiri; ?></div>
                                <div class="timeline-content">
                                    <h5>Pendirian Desa</h5>
                                    <p>Desa <?php echo $nama_desa; ?> resmi berdiri melalui proses pemekaran dari desa induk. Awalnya terdiri dari 3 RW dan 9 RT dengan mayoritas penduduk bekerja sebagai petani.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">1992</div>
                                <div class="timeline-content">
                                    <h5>Pembangunan Balai Desa</h5>
                                    <p>Balai Desa pertama dibangun sebagai pusat administrasi dan kegiatan masyarakat. Jumlah penduduk mencapai 1.200 jiwa.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">2005</div>
                                <div class="timeline-content">
                                    <h5>Pembangunan Jalan Desa</h5>
                                    <p>Jalan utama desa diaspal untuk pertama kalinya, meningkatkan akses transportasi dan perekonomian warga.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">2015</div>
                                <div class="timeline-content">
                                    <h5>Penghargaan Desa Mandiri</h5>
                                    <p>Desa <?php echo $nama_desa; ?> meraih penghargaan sebagai Desa Mandiri tingkat kabupaten berkat pengelolaan BUMDes yang sukses.</p>
                                </div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-year">2020</div>
                                <div class="timeline-content">
                                    <h5>Digitalisasi Administrasi</h5>
                                    <p>Sistem administrasi desa mulai terdigitalisasi dengan peluncuran website resmi dan aplikasi pelayanan masyarakat.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <h3 class="mb-4">Gallery Sejarah</h3>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <a href="../assets/images/gallery/sejarah1.jpg" data-fancybox="gallery" class="d-block overflow-hidden rounded-3 hover-scale">
                                    <img src="../assets/images/gallery/sejarah1.jpg" alt="Gallery 1" class="img-fluid w-100">
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../assets/images/gallery/sejarah2.jpg" data-fancybox="gallery" class="d-block overflow-hidden rounded-3 hover-scale">
                                    <img src="../assets/images/gallery/sejarah2.jpg" alt="Gallery 2" class="img-fluid w-100">
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../assets/images/gallery/sejarah3.jpg" data-fancybox="gallery" class="d-block overflow-hidden rounded-3 hover-scale">
                                    <img src="../assets/images/gallery/sejarah3.jpg" alt="Gallery 3" class="img-fluid w-100">
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../assets/images/gallery/sejarah4.jpg" data-fancybox="gallery" class="d-block overflow-hidden rounded-3 hover-scale">
                                    <img src="../assets/images/gallery/sejarah4.jpg" alt="Gallery 4" class="img-fluid w-100">
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../assets/images/gallery/sejarah5.jpg" data-fancybox="gallery" class="d-block overflow-hidden rounded-3 hover-scale">
                                    <img src="../assets/images/gallery/sejarah5.jpg" alt="Gallery 5" class="img-fluid w-100">
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="../assets/images/gallery/sejarah6.jpg" data-fancybox="gallery" class="d-block overflow-hidden rounded-3 hover-scale">
                                    <img src="../assets/images/gallery/sejarah6.jpg" alt="Gallery 6" class="img-fluid w-100">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.timeline {
    position: relative;
    padding-left: 50px;
    margin: 0 0 30px;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--primary);
}

.timeline-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-year {
    position: absolute;
    left: -50px;
    top: 0;
    width: 40px;
    height: 40px;
    line-height: 40px;
    text-align: center;
    background: var(--primary);
    color: white;
    border-radius: 50%;
    font-weight: bold;
}

.timeline-content {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    position: relative;
}

.timeline-content:before {
    content: '';
    position: absolute;
    left: -10px;
    top: 20px;
    width: 0;
    height: 0;
    border-top: 10px solid transparent;
    border-bottom: 10px solid transparent;
    border-right: 10px solid #f8f9fa;
}
</style>

<?php include '../includes/footer.php'; ?>