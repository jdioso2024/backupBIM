from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('pendaftaran', '0009_pendaftar_sumber_info'),
    ]

    operations = [
        migrations.AddField(
            model_name='registrasi',
            name='nomor_va',
            field=models.CharField(
                blank=True, default='', max_length=40,
                help_text='Nomor Virtual Account pembayaran (dari Sistem Keuangan)',
            ),
        ),
    ]
