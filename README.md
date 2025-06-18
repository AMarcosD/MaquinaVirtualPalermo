# Trabajo Práctico Integrador – Universidad de Palermo

## 🧾 Bitácora de configuración – Puntos 1 a 5

---

### ✅ Parte 1 – Entorno

- Se importó la VM desde archivo `.ova` proporcionado en Blackboard.
- Se blanqueó la contraseña de root utilizando GRUB (`init=/bin/bash`), y se cambió a `palermo`.
- Se configuró el hostname como `TPServer`.
- Se solucionó un problema de red cambiando la interfaz a NAT y forzando DHCP.
- Se configuró posteriormente IP estática en `/etc/network/interfaces` (`192.168.1.33`).

---

### ✅ Parte 2 – Servicios

- Se instaló y configuró `OpenSSH Server`.
- Se habilitó login de root vía **clave pública**, cargando `clave_publica.pub` desde una **carpeta compartida**.
- Se instalaron `Apache` y `PHP 7.4` con:

  ```bash
  apt install apache2 php -y
  ```

- Se verificó funcionamiento web en `http://192.168.1.33`.
- Se instaló `MariaDB` y se ejecutó el script `db.sql` para crear la base `ingenieria` y el usuario `lcars`.
- El archivo `index.php` se conectó correctamente a la base y mostró datos desde el navegador.

---

### ✅ Parte 3 – Configuración de Red

- Se estableció IP estática en `/etc/network/interfaces`:

  ```ini
  auto enp0s3
  iface enp0s3 inet static
      address 192.168.1.33
      netmask 255.255.255.0
      gateway 192.168.1.1
  ```

- Se validó el acceso con `ping` desde la máquina física.

---

### ✅ Parte 4 – Almacenamiento

- Se agregó un nuevo disco de 10 GB a la VM (detectado como `sdc`).
- Se particionó con `fdisk`:
  - `/dev/sdc1` (3 GB) → `/www_dir`
  - `/dev/sdc2` (6 GB) → `/backup_dir`
- Se formatearon ambas particiones como `ext4` y se montaron:

  ```bash
  mount /dev/sdc1 /www_dir
  mount /dev/sdc2 /backup_dir
  ```

- Se copió el contenido del sitio web (`index.php`, `logo.png`) a `/www_dir`.
- Se actualizó el archivo de Apache `000-default.conf`:

  ```apache
  DocumentRoot /www_dir

  <Directory /www_dir>
      Options Indexes FollowSymLinks
      AllowOverride None
      Require all granted
  </Directory>
  ```

- Se agregaron entradas a `/etc/fstab` utilizando UUIDs para montaje automático:

  ```fstab
  UUID=e96100f0-538c-4236-88a6-24a29afa0a65   /www_dir     ext4    defaults    0 2
  UUID=ad9fc000-0213-4deb-addd-f29ddef2d874   /backup_dir  ext4    defaults    0 2
  ```

- Se creó el archivo `/root/particion` con:

  ```bash
  cat /proc/partitions > /root/particion
  ```

---

### ✅ Parte 5 – Backup

- Se creó el script `/opt/scripts/backup_full.sh` que:
  - Acepta 2 argumentos: origen y destino
  - Genera un `.tar.gz` con fecha en formato `YYYYMMDD`
  - Valida existencia de origen/destino
  - Tiene opción `-help`

- Script probado manualmente:

  ```bash
  /opt/scripts/backup_full.sh /var/log /backup_dir
  ```

- Se programó con `crontab`:

  ```cron
  # Todos los días a las 00:00 → /var/log
  0 0 * * * /opt/scripts/backup_full.sh /var/log /backup_dir

  # Lunes, Miércoles y Viernes a las 23:00 → /www_dir
  0 23 * * 1,3,5 /opt/scripts/backup_full.sh /www_dir /backup_dir
  ```

- Se validó con `crontab -l` que ambas tareas estén activas.

---
