import requests, warnings, sys, os
warnings.filterwarnings('ignore')

PVC_PATH = '/mnt/BAA/Archive/pvc-178af66d-0f92-4e8d-92d7-2820b2bd06ee'

# Kredensial diambil dari environment variable (jangan hardcode!)
TRUENAS_AUTH = (os.environ['TRUENAS_USER'], os.environ['TRUENAS_PASS'])

# TrueNAS API chown to www-data (uid=33, gid=33)
r = requests.post(
    'http://10.3.11.52/api/v2.0/filesystem/chown',
    auth=TRUENAS_AUTH,
    json={
        'path': PVC_PATH,
        'uid': 33,
        'gid': 33,
        'options': {'recursive': True}
    },
    timeout=120
)
print('chown status:', r.status_code)
if r.status_code == 200:
    job_id = r.json()
    print('Job ID:', job_id)
    # Poll job status
    import time
    for _ in range(30):
        jr = requests.get(f'http://10.3.11.52/api/v2.0/core/get_jobs?id={job_id}',
            auth=TRUENAS_AUTH)
        jobs = jr.json()
        if jobs:
            state = jobs[0].get('state')
            print(f'  state: {state}')
            if state in ('SUCCESS', 'FAILED'):
                break
        time.sleep(3)
else:
    print(r.text[:200])
