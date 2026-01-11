package com.biblioteca.dao;

import com.biblioteca.model.Cliente;
import java.util.List;

public interface ClienteDAO {
    // CRUD operations
    boolean insertar(Cliente cliente);
    boolean actualizar(Cliente cliente);
    boolean eliminar(int id);

    // Read operations
    Cliente buscarPorId(int id);
    Cliente buscarPorDni(String dni);
    List<Cliente> obtenerTodos();
    List<Cliente> buscarPorNombre(String nombre);
}
