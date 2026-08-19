-- MIGRACION SEGURA PARA UNA BASE EXISTENTE
-- Ejecutar una sola vez si ya tienes el sistema instalado y NO quieres perder datos.

-- 1) Agregar DNI si tu tabla estudiantes todavía no lo tiene.
ALTER TABLE estudiantes ADD COLUMN dni VARCHAR(20) NULL AFTER apellido;

-- 2) Generar un identificador temporal único para registros antiguos sin DNI.
UPDATE estudiantes SET dni = CONCAT('LEGACY', LPAD(id, 8, '0')) WHERE dni IS NULL OR dni = '';
ALTER TABLE estudiantes MODIFY dni VARCHAR(20) NOT NULL;
ALTER TABLE estudiantes ADD UNIQUE KEY uq_estudiantes_dni (dni);

-- 3) Evitar por base de datos más de una entrada o más de una salida del mismo alumno en el mismo día.
-- Si ya existen duplicados antiguos, límpialos antes de ejecutar esta línea.
ALTER TABLE asistencia ADD UNIQUE KEY uq_asistencia_estudiante_fecha_tipo (estudiante_id, fecha, tipo);
