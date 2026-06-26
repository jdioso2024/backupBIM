from django.contrib import admin
from django.urls import path, include, re_path
from django.conf import settings
from django.conf.urls.static import static
from django.views.static import serve

urlpatterns = [
    path('admin/', admin.site.urls),
    path('', include('pendaftaran.urls')),
    path('raport/', include('raport.urls')),
    path('beasiswa/', include('beasiswa.urls')),
    path('umum/', include('umum.urls')),
    path('panel/', include('panel.urls')),
    path('api/monitor/', include('api.urls')),
]

if settings.DEBUG:
    # Dev: Django serve static & media via static() helper (runserver)
    urlpatterns += static(settings.MEDIA_URL, document_root=settings.MEDIA_ROOT)
    urlpatterns += static(settings.STATIC_URL, document_root=settings.STATIC_ROOT)
else:
    # Prod: static di-serve oleh WhiteNoise middleware; media di-serve oleh
    # Django serve view (trafik admin panel rendah, Django aman). Kalau nanti
    # pasang Nginx di depan, blok `location /media/` di Nginx akan override
    # route ini dan tidak pernah sampai ke Django.
    urlpatterns += [
        re_path(r'^media/(?P<path>.*)$', serve, {'document_root': settings.MEDIA_ROOT}),
    ]
