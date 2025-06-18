
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
