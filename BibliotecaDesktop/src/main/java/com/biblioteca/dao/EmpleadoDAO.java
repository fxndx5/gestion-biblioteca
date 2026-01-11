package com.biblioteca.dao;

import com.biblioteca.model.Empleado;
import java.util.List;

public interface EmpleadoDAO {
    boolean autenticar(String usuario, String password);
    Empleado buscarPorId(int id);
    Empleado buscarPorUsuario(String usuario);
    List<Empleado> obtenerTodos();
    boolean insertar(Empleado empleado);
    boolean actualizar(Empleado empleado);
    boolean eliminar(int id);
}