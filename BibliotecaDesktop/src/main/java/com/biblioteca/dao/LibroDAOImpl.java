package com.biblioteca.dao;

import com.biblioteca.model.Libro;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class LibroDAOImpl implements LibroDAO {

    @Override
    public List<Libro> obtenerTodos() {
        List<Libro> libros = new ArrayList<>();
        String sql = "SELECT * FROM libros";

        try (Connection conn = ConexionDB.getConexion();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                Libro libro = new Libro();
                libro.setId(rs.getInt("id"));
                libro.setTitulo(rs.getString("titulo"));
                libro.setAutor(rs.getString("autor"));
                libro.setIsbn(rs.getString("isbn"));
                libro.setEditorial(rs.getString("editorial"));
                libro.setAñoPublicacion(rs.getInt("año_publicacion"));
                libro.setEjemplares(rs.getInt("ejemplares"));
                libro.setDisponibles(rs.getInt("disponibles"));
                libro.setUbicacion(rs.getString("ubicacion"));
                libros.add(libro);
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return libros;
    }

    @Override
    public List<Libro> buscarPorTitulo(String titulo) {
        return List.of();
    }

    @Override
    public List<Libro> buscarPorAutor(String autor) {
        return List.of();
    }

    @Override
    public List<Libro> buscarPorCategoria(int idCategoria) {
        return List.of();
    }

    @Override
    public boolean insertar(Libro libro) {
        String sql = "INSERT INTO libros (titulo, autor, isbn, editorial, año_publicacion, ejemplares, disponibles, ubicacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, libro.getTitulo());
            pstmt.setString(2, libro.getAutor());
            pstmt.setString(3, libro.getIsbn());
            pstmt.setString(4, libro.getEditorial());
            pstmt.setInt(5, libro.getAñoPublicacion());
            pstmt.setInt(6, libro.getEjemplares());
            pstmt.setInt(7, libro.getDisponibles());
            pstmt.setString(8, libro.getUbicacion());

            int filasAfectadas = pstmt.executeUpdate();
            return filasAfectadas > 0;

        } catch (SQLException e) {
            e.printStackTrace();
            return false;
        }
    }

    @Override
    public boolean actualizar(Libro libro) {
        return false;
    }

    @Override
    public boolean eliminar(int id) {
        return false;
    }

    @Override
    public Libro buscarPorId(int id) {
        return null;
    }

    // ... otros métodos (actualizar, eliminar, buscar por id, etc.)
}