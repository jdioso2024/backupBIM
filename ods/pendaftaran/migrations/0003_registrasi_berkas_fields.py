from django.db import migrations, models
import pendaftaran.models


class Migration(migrations.Migration):

    dependencies = [
        ('pendaftaran', '0002_registrasi'),
    ]

    operations = [
        migrations.AddField(
            model_name='registrasi',
            name='pas_foto',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='bukti_bayar_pendaftaran',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='bukti_bayar_sks',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='bukti_bayar_pengembangan',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='akte_lahir',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='ijazah',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='skhun',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='kartu_keluarga',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='hasil_tes_kesehatan',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
        migrations.AddField(
            model_name='registrasi',
            name='hasil_tes_mmpi2',
            field=models.FileField(blank=True, null=True, upload_to=pendaftaran.models.upload_registrasi),
        ),
    ]
