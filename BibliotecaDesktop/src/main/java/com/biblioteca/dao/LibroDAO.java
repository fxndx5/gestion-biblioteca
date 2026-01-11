package com.biblioteca.dao;

import com.biblioteca.model.Libro;
import java.util.List;

public interface LibroDAO {
    boolean insertar(Libro libro);
    boolean actualizar(Libro libro);
    boolean eliminar(int id);
    Libro buscarPorId(int id);
    List<Libro> obtenerTodos();
    List<Libro> buscarPorTitulo(String titulo);
    List<Libro> buscarPorAutor(String autor);
    List<Libro> buscarPorCategoria(int idCategoria);
}
