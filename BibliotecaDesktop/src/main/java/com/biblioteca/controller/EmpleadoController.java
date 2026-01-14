package com.biblioteca.controller;

import com.biblioteca.dao.EmpleadoDAO;
import com.biblioteca.dao.EmpleadoDAOImpl;
import com.biblioteca.model.Empleado;
import java.util.List;

public class EmpleadoController {
    private EmpleadoDAO empleadoDAO;

    public EmpleadoController() {
        this.empleadoDAO = new EmpleadoDAOImpl();
    }

    public boolean agregarEmpleado(Empleado empleado) {
        return empleadoDAO.insertar(empleado);
    }

    public boolean actualizarEmpleado(Empleado empleado) {
        return empleadoDAO.actualizar(empleado);
    }

    public boolean eliminarEmpleado(int id) {
        return empleadoDAO.eliminar(id);
    }

    public Empleado buscarEmpleadoPorId(int id) {
        return empleadoDAO.buscarPorId(id);
    }

    public List<Empleado> obtenerTodosEmpleados() {
        return empleadoDAO.obtenerTodos();
    }

    public Empleado buscarEmpleadoPorUsuario(String usuario) {
        return empleadoDAO.buscarPorUsuario(usuario);
    }

    public int contarEmpleadosActivos() {
        List<Empleado> empleados = empleadoDAO.obtenerTodos();
        if (empleados == null) return 0;
        int activos = 0;
        for (Empleado e : empleados) {
            if (e.isActivo()) {
                activos++;
            }
        }
        return activos;
    }

    public int contarTotalEmpleados() {
        List<Empleado> empleados = empleadoDAO.obtenerTodos();
        return empleados == null ? 0 : empleados.size();
    }
    public boolean cambiarEstadoEmpleado(int id, boolean activo) {
        // Buscar el empleado por ID
        Empleado empleado = empleadoDAO.buscarPorId(id);
        if (empleado != null) {
            // Cambiar el estado
            empleado.setActivo(activo);
            // Actualizar en la base de datos
            return empleadoDAO.actualizar(empleado);
        }
        return false;
    }
}
