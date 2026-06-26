import paramiko, warnings, os
warnings.filterwarnings('ignore')

PVC_PATH = '/mnt/BAA/Archive/pvc-178af66d-0f92-4e8d-92d7-2820b2bd06ee'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
# Kredensial diambil dari environment variable (jangan hardcode!)
ssh.connect('10.3.11.52', username=os.environ['NFS_SSH_USER'], password=os.environ['NFS_SSH_PASS'])
sftp = ssh.open_sftp()

# Overwrite .env with k8s version
print('Fixing .env on NFS...')
sftp.put('D:/bim/pmb/app/.env.k8s', f'{PVC_PATH}/.env')
print('.env updated with K8s config')

# Remove .env.k8s (not needed on server)
try:
    sftp.remove(f'{PVC_PATH}/.env.k8s')
    print('.env.k8s removed')
except:
    pass

# Verify
with sftp.open(f'{PVC_PATH}/.env', 'r') as f:
    content = f.read().decode()[:200]
    print('Verify .env:', content[:100])

sftp.close()
ssh.close()
