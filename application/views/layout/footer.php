        </main>

        <footer>
            <span>© <?= date('Y') ?> Dashboard Ekonomi Daerah <?= htmlspecialchars($nama_wilayah ?? 'Kabupaten Banyuwangi') ?></span>
            <span>Platform Intelijen Ekonomi & Data Pembangunan Daerah</span>
        </footer>
    </div>
</div>

<?php if (isset($active_page) && $active_page === 'peta_ekonomi'): ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<?php endif; ?>
<script src="<?= base_url('assets/js/app.js?v=' . (file_exists(FCPATH . 'assets/js/app.js') ? filemtime(FCPATH . 'assets/js/app.js') : time())) ?>"></script>
</body>
</html>

