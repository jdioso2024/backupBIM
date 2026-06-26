from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('pendaftaran', '0008_alamat_is_wna'),
    ]

    operations = [
        migrations.AddField(
            model_name='pendaftar',
            name='sumber_info',
            field=models.CharField(
                blank=True,
                choices=[
                    ('web', 'Web'),
                    ('keluarga', 'Keluarga'),
                    ('sekolah', 'Sekolah'),
                    ('marketing', 'Marketing BIM'),
                ],
                max_length=20,
            ),
        ),
        migrations.AddField(
            model_name='pendaftar',
            name='sumber_info_nama',
            field=models.CharField(blank=True, max_length=100),
        ),
    ]
