import paramiko, warnings, os
warnings.filterwarnings('ignore')

# Kredensial & password baru diambil dari environment variable (jangan hardcode!)
NEW_PASSWORD = os.environ['NEW_ADMIN_PASSWORD']

# Step 1: Generate bcrypt hash via SSH ke Nginx server (ada PHP)
print('Connecting to Nginx server untuk generate hash...')
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('10.3.11.60', username=os.environ['NGINX_SSH_USER'], password=os.environ['NGINX_SSH_PASS'])

cmd = f"python3 -c \"import bcrypt; h=bcrypt.hashpw(b'{NEW_PASSWORD}', bcrypt.gensalt(12)); print(h.decode())\""
stdin, stdout, stderr = ssh.exec_command(cmd)
bcrypt_hash = stdout.read().decode().strip()
err = stderr.read().decode().strip()
ssh.close()

if err:
    print('STDERR:', err)

# Laravel menerima $2b$ juga, tapi ganti ke $2y$ untuk kompatibel penuh
bcrypt_hash = bcrypt_hash.replace('$2b$', '$2y$')

if not bcrypt_hash.startswith('$2y$'):
    print('ERROR: Gagal generate hash:', bcrypt_hash)
    exit(1)

print(f'Hash generated: {bcrypt_hash[:30]}...')

# Step 2: Update password di MariaDB
import pymysql
print('Connecting ke MariaDB...')
conn = pymysql.connect(
    host='10.3.11.24',
    user=os.environ['DB_USER'],
    password=os.environ['DB_PASS'],
    database='pmb_bimacid',
    port=3306
)
cur = conn.cursor()

emails = ['superadmin@bim.ac.id', 'admin@bim.ac.id']
for email in emails:
    rows = cur.execute(
        "UPDATE users SET password=%s, original_password=%s WHERE email=%s",
        (bcrypt_hash, NEW_PASSWORD, email)
    )
    print(f'  Updated {email}: {rows} row(s) affected')

conn.commit()
cur.close()
conn.close()

print('\nSelesai! Password baru sudah di-set sesuai NEW_ADMIN_PASSWORD.')
print('Login di: https://pmb.bim.ac.id/login')
print('  superadmin@bim.ac.id')
print('  admin@bim.ac.id')
