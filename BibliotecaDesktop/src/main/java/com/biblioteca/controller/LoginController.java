package com.biblioteca.controller;

import com.biblioteca.dao.EmpleadoDAO;
import com.biblioteca.dao.EmpleadoDAOImpl;
import com.biblioteca.model.Empleado;

public class LoginController {
    private EmpleadoDAO empleadoDAO;

    public LoginController() {
        this.empleadoDAO = new EmpleadoDAOImpl();
    }

    public boolean autenticar(String usuario, String password) {
        return empleadoDAO.autenticar(usuario, password);
    }

    public Empleado obtenerEmpleadoPorUsuario(String usuario) {
        return empleadoDAO.buscarPorUsuario(usuario);
    }
}