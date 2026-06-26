import paramiko, os, sys
sys.stdout.reconfigure(encoding='utf-8')

PVC = '/mnt/BAA/Archive/pvc-178af66d-0f92-4e8d-92d7-2820b2bd06ee'
LOCAL = 'D:/bim/pmb/app'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
# Kredensial diambil dari environment variable (jangan hardcode!)
ssh.connect('10.3.11.52', username=os.environ['NFS_SSH_USER'], password=os.environ['NFS_SSH_PASS'])

# chmod file yang ada dulu
existing = [
    'resources/views/welcome.blade.php',
    'resources/views/layouts/navbar.blade.php',
    'app/Http/Controllers/StudentController.php',
    'app/Http/Controllers/Superadmin/SettingController.php',
    'resources/views/pages/superadmin/setting/index.blade.php',
]
for f in existing:
    ssh.exec_command(f'sudo chmod 666 {PVC}/{f}')

sftp = ssh.open_sftp()

files = [
    'database/migrations/2026_04_22_000002_seed_additional_settings.php',
    'app/Http/Controllers/Superadmin/SettingController.php',
    'resources/views/pages/superadmin/setting/index.blade.php',
    'resources/views/welcome.blade.php',
    'resources/views/layouts/navbar.blade.php',
    'app/Http/Controllers/StudentController.php',
]

for f in files:
    local_path = os.path.join(LOCAL, f).replace('\\', '/')
    remote_path = f'{PVC}/{f}'
    sftp.put(local_path, remote_path)
    print(f'  uploaded: {f}')

# Script migrate + clear cache
migrate_php = '''<?php
require __DIR__."/../vendor/autoload.php";
$app = require_once __DIR__."/../bootstrap/app.php";
$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
Artisan::call("migrate", ["--path"=>"database/migrations/2026_04_22_000002_seed_additional_settings.php","--force"=>true]);
echo "Migrate: ".Artisan::output()."<br>";
Artisan::call("route:clear");  echo "route:clear OK<br>";
Artisan::call("view:clear");   echo "view:clear OK<br>";
Artisan::call("config:clear"); echo "config:clear OK<br>";
echo "DONE";
unlink(__FILE__);
'''
with sftp.open(f'{PVC}/public/migrate2.php', 'w') as fh:
    fh.write(migrate_php)

sftp.close()
ssh.close()
print('\nDone! Akses: https://pmb.bim.ac.id/migrate2.php')
