from django.db import migrations, models
import django.db.models.deletion


class Migration(migrations.Migration):

    dependencies = [
        ('core', '0001_initial'),
        ('pendaftaran', '0007_alter_registrasi_kelas'),
    ]

    operations = [
        migrations.AddField(
            model_name='alamat',
            name='is_wna',
            field=models.BooleanField(default=False),
        ),
        migrations.AlterField(
            model_name='alamat',
            name='kota',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.PROTECT, to='core.kota'),
        ),
        migrations.AlterField(
            model_name='alamat',
            name='provinsi',
            field=models.ForeignKey(blank=True, null=True, on_delete=django.db.models.deletion.PROTECT, to='core.provinsi'),
        ),
    ]
