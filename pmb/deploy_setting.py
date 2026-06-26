import paramiko, os, sys
sys.stdout.reconfigure(encoding='utf-8')

PVC = '/mnt/BAA/Archive/pvc-178af66d-0f92-4e8d-92d7-2820b2bd06ee'
LOCAL = 'D:/bim/pmb/app'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
# Kredensial diambil dari environment variable (jangan hardcode!)
ssh.connect('10.3.11.52', username=os.environ['NFS_SSH_USER'], password=os.environ['NFS_SSH_PASS'])
sftp = ssh.open_sftp()

files = [
    'database/migrations/2026_04_22_000001_create_settings_table.php',
    'app/Models/Setting.php',
    'app/Http/Controllers/Superadmin/SettingController.php',
    'resources/views/pages/superadmin/setting/index.blade.php',
    'resources/views/layouts/superadmin/includes/sidebar.blade.php',
    'resources/views/layouts/navbar.blade.php',
    'resources/views/welcome.blade.php',
    'routes/superadmin.php',
    'app/Http/Controllers/StudentController.php',
]

def sftp_mkdir_p(sftp, path):
    parts = path.split('/')
    cur = ''
    for p in parts:
        if not p:
            continue
        cur = cur + '/' + p
        try:
            sftp.stat(cur)
        except FileNotFoundError:
            sftp.mkdir(cur)

for f in files:
    local_path = os.path.join(LOCAL, f).replace('\\', '/')
    remote_path = f'{PVC}/{f}'
    remote_dir = '/'.join(remote_path.split('/')[:-1])
    sftp_mkdir_p(sftp, remote_dir)
    sftp.put(local_path, remote_path)
    print(f'  uploaded: {f}')

# Jalankan migration
print('\nRunning migration...')
# Tulis script PHP untuk jalankan migration
migrate_php = '''<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
try {
    Artisan::call('migrate', ['--path' => 'database/migrations/2026_04_22_000001_create_settings_table.php', '--force' => true]);
    echo 'Migration OK: ' . Artisan::output();
} catch (Exception $e) {
    echo 'Migration error: ' . $e->getMessage();
}
// Clear caches
Artisan::call('view:clear');
Artisan::call('config:clear');
echo 'Cache cleared.';
unlink(__FILE__);
'''
with sftp.open(f'{PVC}/public/run_migrate.php', 'w') as fh:
    fh.write(migrate_php)
print('Migration script ready: https://pmb.bim.ac.id/run_migrate.php')

sftp.close()
ssh.close()
print('\nDone! Buka browser dan akses https://pmb.bim.ac.id/run_migrate.php')
