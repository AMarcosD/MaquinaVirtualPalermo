
# Trabajo Práctico Integrador – Universidad de Palermo

## README – Paso a paso completo con diagnóstico y solución

---

## 🧩 Punto 1 – Entorno: Configuración inicial de la máquina virtual

### 1) Diagnóstico principal

Al importar la máquina virtual provista por la cátedra (archivo `.ova`), nos encontramos con dos grandes obstáculos iniciales:

- ❌ El usuario y la contraseña no estaban documentados.
- ❌ La máquina no tenía acceso a Internet desde el entorno NAT de VirtualBox.

Ambos problemas impedían continuar con cualquier configuración o instalación dentro de la VM.

---

### 2) Solución implementada

#### 🔐 Blanqueo de contraseña de root

Se forzó el ingreso como root sin conocer la clave, editando el arranque desde GRUB:

```bash
# En la pantalla de GRUB (presionar 'e')
linux /boot/vmlinuz... ro quiet splash → reemplazar por:
linux /boot/vmlinuz... init=/bin/bash
```

> Esto inicia la VM en un shell de root sin necesidad de contraseña.

Luego se remonta el sistema de archivos con permisos de escritura:

```bash
mount -o remount,rw /
```

Se cambia la contraseña de root:

```bash
passwd
```

Y finalmente se reinicia:

```bash
exec /sbin/init
```

---

#### 🌐 Configuración de red (sin acceso a Internet)

Al iniciar, la VM no podía resolver dominios ni hacer ping externo. Se revisó con:

```bash
ip a
```

Se detectó que la interfaz tenía IP del rango `169.254.x.x`, lo que indica fallo de DHCP.

📌 Se corrigió configurando el adaptador de VirtualBox en modo **NAT**, y luego forzando DHCP con:

```bash
dhclient enp0s3
```

🛠️ Se validó que la interfaz ahora toma IP tipo `10.0.2.x` o `192.168.x.x`.

---

### 3) Resultado

- Se logró acceder como root con contraseña nueva (`palermo`).
- Se verificó conectividad con Internet ejecutando:

```bash
ping -c 3 google.com
```

- Se desbloqueó completamente la capacidad de trabajar dentro de la VM (instalar paquetes, actualizar, etc).

---

### 4) Estado actual

- La máquina virtual está activa con acceso como root.
- El hostname fue configurado como `TPServer` con:

```bash
hostnamectl set-hostname TPServer
```

- La red funciona correctamente y permite navegación, descarga e instalación de paquetes.

---

## 🧩 Punto 2 – Servicios: SSH, Apache, PHP y MariaDB

### 1) Diagnóstico principal

Luego de tener acceso como root y red operativa, se debía convertir la VM en un servidor funcional. El trabajo requería:

- Acceso remoto por SSH
- Un servidor web operativo con Apache y PHP
- Un motor de base de datos MariaDB funcional
- Conexión entre el servidor web (PHP) y la base de datos

Problemas detectados:

- ❌ No estaba instalado `openssh-server`
- ❌ No se podía copiar fácilmente la clave pública desde el host
- ❌ Faltaban los paquetes `apache2`, `php`, `mariadb-server`, `php-mysql`
- ❌ El archivo `index.php` requería conexión a base de datos y no se conectaba

---

### 2) Solución implementada

#### 🔐 Instalación de SSH y autenticación por clave pública

Se instaló el servidor SSH:

```bash
apt update && apt install openssh-server -y
```

Para evitar el uso de contraseña, se habilitó autenticación por clave pública. Se copió `clave_publica.pub` desde una carpeta compartida montada y se configuró:

```bash
mkdir -p /root/.ssh
cp /media/sf_vm_share/clave_publica.pub /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys
chmod 700 /root/.ssh
```

Se verificó acceso desde el host con:

```bash
ssh -i /ruta/a/clave_privada.txt root@IP_VM
```

---

#### 🌐 Carpeta compartida entre Windows y la VM

Se montó la carpeta de VirtualBox manualmente:

```bash
mount -t vboxsf vm_share /media/sf_vm_share
```

---

#### 🌐 Instalación de Apache y PHP

Se instalaron los servicios:

```bash
apt install apache2 php libapache2-mod-php -y
```

---

#### 🛠️ Instalación y configuración de MariaDB

```bash
apt install mariadb-server php-mysql -y
```

Se creó la base de datos y tablas con el script `db.sql`, importado desde la carpeta compartida:

```bash
mysql < /media/sf_vm_share/db.sql
```

Se creó el usuario y se dieron permisos:

```sql
GRANT ALL PRIVILEGES ON ingenieria.* TO 'lcars'@'localhost' IDENTIFIED BY 'NCC1701D';
FLUSH PRIVILEGES;
```

---

#### 🖥️ Configuración del archivo `index.php`

Se movió `index.php` y `logo.png` a `/var/www/html`. El archivo PHP intentaba conectarse a la base usando `mysqli`.

Problemas:
- ❌ Error HTTP 500 → se debió a permisos incorrectos
- ❌ Access denied → usuario mal creado o no existente

Solución:
- Se ajustaron permisos con `chmod` y `chown`
- Se corrigió el usuario y contraseña, y se validó con logs:

```bash
tail -f /var/log/apache2/error.log
```

---

### 3) Resultado

- Apache responde correctamente en `http://192.168.1.33/index.php`
- El archivo `index.php` se conecta a MariaDB y muestra los datos correctamente
- SSH funcional con clave desde el host

---

### 4) Estado actual

- Apache, PHP y MariaDB están instalados, activos y funcionando
- El usuario `lcars` tiene acceso a la base `ingenieria`
- El contenido web se encuentra en `/var/www/html`
- Se puede acceder por SSH usando clave sin contraseña

---

## 🧩 Punto 3 – Configuración de red con IP estática

### 1) Diagnóstico principal

La VM inicialmente funcionaba con IP asignada por DHCP. Esto generaba:

- Incertidumbre sobre qué IP tendría la máquina en cada arranque.
- Problemas para acceder desde el navegador o por SSH si la IP cambiaba.
- Inconvenientes para conectar servicios de forma predecible.

Se necesitaba una **IP estática persistente**, como lo exige el TP.

---

### 2) Solución implementada

#### 📌 Obtención de parámetros de red actuales

Se ejecutó:

```bash
ip a
ip route
```

Esto permitió relevar:

- Interfaz activa: `enp0s3`
- IP asignada: `192.168.1.33`
- Gateway: `192.168.1.1`

---

#### ✍️ Configuración de IP estática

Se editó el archivo `/etc/network/interfaces`:

```bash
vi /etc/network/interfaces
```

Y se reemplazó el contenido relacionado con `enp0s3` por:

```ini
auto enp0s3
iface enp0s3 inet static
    address 192.168.1.33
    netmask 255.255.255.0
    gateway 192.168.1.1
```

---

#### 🔁 Reinicio de red

Para aplicar cambios sin reiniciar el sistema:

```bash
ifdown enp0s3 && ifup enp0s3
```

O se reinició la VM completamente para garantizar la persistencia.

---

### 3) Resultado

- La VM tomó correctamente la IP `192.168.1.33` de forma fija.
- Se pudo acceder a los servicios desde el navegador y SSH sin depender del DHCP.
- La IP quedó reservada y funcional a través de todos los reinicios.

---

### 4) Estado actual

- La interfaz `enp0s3` tiene configuración estática.
- El archivo `/etc/network/interfaces` contiene los valores requeridos.
- La red opera de forma estable y predecible.

---

## 🧩 Punto 4 – Almacenamiento: discos, montaje y Apache

### 1) Diagnóstico principal

El trabajo requería:

- Agregar un disco extra de 10 GB
- Crear dos particiones: una de 3 GB para el sitio web y otra de 6 GB para backups
- Montar esas particiones en `/www_dir` y `/backup_dir`
- Configurar Apache para que sirva contenido desde `/www_dir`
- Asegurar montaje automático con `fstab`
- Generar archivo `/proc/particion` desde `/proc/partitions`

Problemas encontrados:

- ❌ El nuevo disco no era detectado hasta reiniciar la VM
- ❌ Apache arrojaba error 403 cuando se cambió la raíz del sitio
- ❌ El archivo `/proc/particion` no puede persistirse dentro de `/proc`

---

### 2) Solución implementada

#### 💽 Creación del disco adicional

Desde VirtualBox se agregó un disco de 10 GB. Al reiniciar la VM, se detectó como `/dev/sdc`.

#### 📐 Particionamiento y formato

Se usó `fdisk`:

```bash
fdisk /dev/sdc
```

Se crearon dos particiones:
- `/dev/sdc1` de 3 GB
- `/dev/sdc2` de 6 GB

Ambas se formatearon como `ext4`:

```bash
mkfs.ext4 /dev/sdc1
mkfs.ext4 /dev/sdc2
```

---

#### 📂 Creación de puntos de montaje y montaje manual

```bash
mkdir /www_dir
mkdir /backup_dir

mount /dev/sdc1 /www_dir
mount /dev/sdc2 /backup_dir
```

---

#### 🛠️ Modificación de Apache

Se editó `/etc/apache2/sites-available/000-default.conf`:

```apache
DocumentRoot /www_dir

<Directory /www_dir>
    Options Indexes FollowSymLinks
    AllowOverride None
    Require all granted
</Directory>
```

Y se reinició Apache:

```bash
systemctl restart apache2
```

Se solucionó el error 403 asegurando permisos de lectura:

```bash
chmod -R 755 /www_dir
```

---

#### 📌 Montaje automático con fstab

Se obtuvieron los UUIDs con:

```bash
blkid
```

Se editó `/etc/fstab`:

```fstab
UUID=... /www_dir     ext4    defaults    0 2
UUID=... /backup_dir  ext4    defaults    0 2
```

---

#### 📝 Creación de archivo de particiones

```bash
cat /proc/partitions > /root/particion
```

Opcional: se puede hacer un symlink en `/proc`, aunque es efímero.

---

### 3) Resultado

- El sitio web funciona desde `/www_dir`
- Ambas particiones están montadas y operativas
- El contenido persiste y se monta automáticamente al iniciar
- Se creó `/root/particion` como lo requiere el TP

---

### 4) Estado actual

- Disco `/dev/sdc` con dos particiones activas
- Apache sirve desde `/www_dir`
- `/backup_dir` listo para almacenar backups
- Configuración permanente validada en `/etc/fstab`

---

## 🧩 Punto 5 – Script de Backup y Automatización

### 1) Diagnóstico principal

El TP requería automatizar copias de seguridad de dos directorios distintos (`/var/log` y `/www_dir`) hacia `/backup_dir`. Los requisitos incluían:

- Un script llamado `backup_full.sh` alojado en `/opt/scripts/`
- Backup comprimido `.tar.gz` con la fecha en el nombre
- Validaciones de existencia de origen y destino
- Uso de argumentos para directorios
- Incluir un `-help`
- Programación automática con `cron`

---

### 2) Solución implementada

#### 📁 Creación de carpeta y script

Se creó la carpeta:

```bash
mkdir -p /opt/scripts
```

Se creó y editó el archivo `/opt/scripts/backup_full.sh`:

```bash
vi /opt/scripts/backup_full.sh
```

Contenido del script (resumen de comportamiento):

- Verifica si el usuario pidió ayuda con `-help`
- Chequea si se pasaron exactamente dos argumentos
- Valida existencia de los directorios
- Genera un archivo con formato:
  `/backup_dir/NOMBREORIGEN_bkp_YYYYMMDD.tar.gz`
- Usa `tar` para comprimir el contenido

Se le dieron permisos de ejecución:

```bash
chmod +x /opt/scripts/backup_full.sh
```

---

#### 🧪 Pruebas del script

Se probó con:

```bash
/opt/scripts/backup_full.sh /var/log /backup_dir
```

Y se validó el contenido con:

```bash
ls /backup_dir
```

---

#### ⏰ Automatización con cron

Se editó el crontab de root:

```bash
crontab -e
```

Y se agregaron las siguientes líneas:

```cron
# Todos los días a las 00:00 → /var/log
0 0 * * * /opt/scripts/backup_full.sh /var/log /backup_dir

# Lunes, Miércoles y Viernes a las 23:00 → /www_dir
0 23 * * 1,3,5 /opt/scripts/backup_full.sh /www_dir /backup_dir
```

Se validó con:

```bash
crontab -l
```

---

### 3) Resultado

- El script realiza backups comprimidos correctamente
- Se validan argumentos y existencia de directorios
- Los archivos generados tienen fecha e identificación
- Las tareas se ejecutan automáticamente según cron

---

### 4) Estado actual

- Script funcional y alojado en `/opt/scripts/backup_full.sh`
- Automatización activa vía `cron`
- Backups confirmados en `/backup_dir`


