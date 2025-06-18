# MaquinaVirtualPalermo
Como parte del proyecto final de la cursada de computacion aplicada, se creo y configuro un entorno virtual con caracteristicas determinadas.

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

✅ Se verificó funcionamiento web en http://192.168.1.33.

✅ Se instaló MariaDB y se ejecutó el script db.sql para crear la base ingenieria y el usuario lcars.

✅ El archivo index.php se conectó correctamente a la base y mostró datos desde el navegador.

---

### ✅ Parte 3 – Configuración de Red

- Se estableció IP estática en /etc/network/interfaces:

auto enp0s3
iface enp0s3 inet static
    address 192.168.1.33
    netmask 255.255.255.0
    gateway 192.168.1.1
    
✅ Se validó el acceso con ping desde la máquina física.

### ✅ Parte 4 - Almacenamiento

- Se agregó un nuevo disco de 10 GB a la VM (disco sdc).
- Se particionó con fdisk:
- /dev/sdc1 (3 GB) → /www_dir
- /dev/sdc2 (6 GB) → /backup_dir


 

