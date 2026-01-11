package com.biblioteca.controller;

import com.biblioteca.model.Cliente;
import com.biblioteca.dao.ClienteDAO;
import com.biblioteca.dao.ClienteDAOImpl;

import java.util.Collection;
import java.util.List;

public class ClienteController {
    private ClienteDAO clienteDAO;

    public ClienteController() {
        this.clienteDAO = new ClienteDAOImpl();
    }

    public boolean registrarCliente(Cliente cliente) {
        return clienteDAO.insertar(cliente);
    }

    public boolean actualizarCliente(Cliente cliente) {
        return clienteDAO.actualizar(cliente);
    }

    public boolean eliminarCliente(int id) {
        return clienteDAO.eliminar(id);
    }

    public Cliente buscarClientePorId(int id) {
        return clienteDAO.buscarPorId(id);
    }

    public Cliente buscarClientePorDni(String dni) {
        return clienteDAO.buscarPorDni(dni);
    }

    public List<Cliente> obtenerTodosClientes() {
        return clienteDAO.obtenerTodos();
    }

    public List<Cliente> buscarClientesPorNombre(String nombre) {
        return clienteDAO.buscarPorNombre(nombre);
    }

    public boolean cambiarEstadoSancion(int idCliente, boolean sancionado) {
        Cliente cliente = clienteDAO.buscarPorId(idCliente);
        if (cliente != null) {
            cliente.setSancionado(sancionado);
            return clienteDAO.actualizar(cliente);
        }
        return false;
    }

    public boolean puedeHacerPrestamos(int idCliente) {
        Cliente cliente = clienteDAO.buscarPorId(idCliente);
        return cliente != null && !cliente.isSancionado();
    }

    public Object[] obtenerEstadisticasClientes() {
        List<Cliente> clientes = clienteDAO.obtenerTodos();
        if (clientes == null || clientes.isEmpty()) {
            return new Object[]{0, 0, 0};
        }

        int total = clientes.size();
        int sancionados = 0;
        int activos = 0;

        for (Cliente c : clientes) {
            if (c.isSancionado()) {
                sancionados++;
            } else {
                activos++;
            }
        }

        return new Object[]{total, activos, sancionados};
    }


}
