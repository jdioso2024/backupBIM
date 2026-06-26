from .models import Setting, SiteSetting


def site_settings(request):
    ctx = {s.key: s.value for s in Setting.objects.all()}
    try:
        ctx['site'] = SiteSetting.get_instance()
    except Exception:
        ctx['site'] = None
    # Flag impersonation utk banner di base.html
    ctx['is_impersonating']      = bool(request.session.get('impersonator_id'))
    ctx['impersonator_username'] = request.session.get('impersonator_username', '')
    return ctx
