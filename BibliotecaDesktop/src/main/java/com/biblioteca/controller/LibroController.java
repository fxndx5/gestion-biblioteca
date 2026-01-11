package com.biblioteca.controller;

import com.biblioteca.dao.LibroDAO;
import com.biblioteca.dao.LibroDAOImpl;
import com.biblioteca.model.Libro;
import java.util.List;

public class LibroController {
    private LibroDAO libroDAO;

    public LibroController() {
        this.libroDAO = new LibroDAOImpl();
    }

    public boolean agregarLibro(Libro libro) {
        return libroDAO.insertar(libro);
    }

    public boolean actualizarLibro(Libro libro) {
        return libroDAO.actualizar(libro);
    }

    public boolean eliminarLibro(int id) {
        return libroDAO.eliminar(id);
    }

    public Libro buscarLibroPorId(int id) {
        return libroDAO.buscarPorId(id);
    }

    public List<Libro> obtenerTodosLosLibros() {
        return libroDAO.obtenerTodos();
    }

    public List<Libro> buscarLibrosPorTitulo(String titulo) {
        return libroDAO.buscarPorTitulo(titulo);
    }

    public List<Libro> buscarLibrosPorAutor(String autor) {
        return libroDAO.buscarPorAutor(autor);
    }
}