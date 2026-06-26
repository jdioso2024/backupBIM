from django.db import migrations, models


class Migration(migrations.Migration):

    dependencies = [
        ('core', '0006_biayakuliahprodi_biaya_hidup_and_more'),
    ]

    operations = [
        migrations.AddField(
            model_name='prodi',
            name='kuota',
            field=models.PositiveIntegerField(
                default=0,
                help_text='Kuota pendaftar untuk PMB berjalan. 0 = belum diset.',
            ),
        ),
    ]
