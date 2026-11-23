        </main>

        <footer>
            <span>© <?= date('Y') ?> Dashboard Ekonomi Daerah <?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?></span>
            <span>Platform Intelijen Ekonomi & Data Pembangunan Daerah</span>
        </footer>
    </div>
</div>

<script src="<?= base_url('assets/js/app.js?v=' . (file_exists(FCPATH . 'assets/js/app.js') ? filemtime(FCPATH . 'assets/js/app.js') : time())) ?>"></script>
</body>
</html>

