import paramiko, os, sys, stat
sys.stdout.reconfigure(encoding='utf-8')

PVC_PATH = '/mnt/BAA/Archive/pvc-178af66d-0f92-4e8d-92d7-2820b2bd06ee'
LOCAL_PATH = 'D:/bim/pmb/app'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
# Kredensial diambil dari environment variable (jangan hardcode!)
ssh.connect('10.3.11.52', username=os.environ['NFS_SSH_USER'], password=os.environ['NFS_SSH_PASS'])
sftp = ssh.open_sftp()

total_files = 0
total_bytes = 0

def sftp_mkdir(sftp, path):
    try:
        sftp.stat(path)
    except FileNotFoundError:
        sftp_mkdir(sftp, os.path.dirname(path).replace('\\', '/'))
        sftp.mkdir(path)

def upload_dir(local, remote):
    global total_files, total_bytes
    try:
        sftp.stat(remote)
    except FileNotFoundError:
        sftp.mkdir(remote)

    for item in os.listdir(local):
        local_path = os.path.join(local, item)
        remote_path = f'{remote}/{item}'

        if os.path.isdir(local_path):
            upload_dir(local_path, remote_path)
        else:
            size = os.path.getsize(local_path)
            sftp.put(local_path, remote_path)
            total_files += 1
            total_bytes += size
            if total_files % 200 == 0:
                print(f'Uploaded {total_files} files ({total_bytes/1024/1024:.1f}MB)')

# Upload .env (k8s version)
print('Uploading .env...')
sftp.put('D:/bim/pmb/app/.env.k8s', f'{PVC_PATH}/.env')

# Upload all app files
print(f'Uploading app files to {PVC_PATH}...')
upload_dir(LOCAL_PATH, PVC_PATH)

sftp.close()
ssh.close()
print(f'\nDone! {total_files} files ({total_bytes/1024/1024:.1f}MB) uploaded to {PVC_PATH}')
