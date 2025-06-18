
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
