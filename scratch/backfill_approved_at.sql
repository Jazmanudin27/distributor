-- =================================================================
-- BACKFILL approved_at di tabel canvas_sessions
-- Jalankan di phpMyAdmin, MySQL Workbench, atau tool DB lainnya
-- =================================================================

-- Step 1: Cek dulu kondisi sebelum diupdate
SELECT id, no_canvas, kode_sales, status, created_at, updated_at, approved_at
FROM canvas_sessions
WHERE approved_at IS NULL
ORDER BY id;

-- ─────────────────────────────────────────────────────────────────
-- Step 2a: Session LOADING → approved_at = updated_at
--   (updated_at ≈ waktu approve, karena approve adalah satu-satunya
--    aksi yang mengubah status ke 'loading')
-- ─────────────────────────────────────────────────────────────────
UPDATE canvas_sessions
SET approved_at = updated_at
WHERE approved_at IS NULL
  AND status = 'loading';

-- ─────────────────────────────────────────────────────────────────
-- Step 2b: Session COMPLETED → approved_at = created_at (fallback)
--   (updated_at pada completed = waktu setoran, bukan waktu approve.
--    Fallback ke created_at mempertahankan perilaku query lama.)
-- ─────────────────────────────────────────────────────────────────
UPDATE canvas_sessions
SET approved_at = created_at
WHERE approved_at IS NULL
  AND status = 'completed';

-- Step 3: Verifikasi hasil
SELECT id, no_canvas, kode_sales, status,
       created_at, updated_at, approved_at,
       TIMESTAMPDIFF(MINUTE, created_at, approved_at) AS menit_tunggu_approve
FROM canvas_sessions
ORDER BY id;
