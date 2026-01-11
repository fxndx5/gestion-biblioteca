package com.biblioteca.dao;

import com.biblioteca.model.Empleado;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class EmpleadoDAOImpl implements EmpleadoDAO {

    @Override
    public boolean autenticar(String usuario, String password) {
        String sql = "SELECT id FROM empleados WHERE usuario = ? AND password = SHA2(?, 256) AND activo = 1";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, usuario);
            pstmt.setString(2, password);
            ResultSet rs = pstmt.executeQuery();

            return rs.next();

        } catch (SQLException e) {
            System.err.println("Error al autenticar empleado: " + e.getMessage());
            return false;
        }
    }

    @Override
    public Empleado buscarPorId(int id) {
        String sql = "SELECT * FROM empleados WHERE id = ?";
        Empleado empleado = null;

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setInt(1, id);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                empleado = new Empleado();
                empleado.setId(rs.getInt("id"));
                empleado.setDni(rs.getString("dni"));
                empleado.setNombre(rs.getString("nombre"));
                empleado.setEmail(rs.getString("email"));
                empleado.setTelefono(rs.getString("telefono"));
                empleado.setCargo(rs.getString("cargo"));
                empleado.setUsuario(rs.getString("usuario"));
                empleado.setPassword(rs.getString("password"));
                empleado.setFechaRegistro(rs.getString("fecha_registro"));
                empleado.setActivo(rs.getBoolean("activo"));
            }

        } catch (SQLException e) {
            System.err.println("Error al buscar empleado por ID: " + e.getMessage());
        }

        return empleado;
    }

    @Override
    public Empleado buscarPorUsuario(String usuario) {
        String sql = "SELECT * FROM empleados WHERE usuario = ?";
        Empleado empleado = null;

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, usuario);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                empleado = new Empleado();
                empleado.setId(rs.getInt("id"));
                empleado.setDni(rs.getString("dni"));
                empleado.setNombre(rs.getString("nombre"));
                empleado.setEmail(rs.getString("email"));
                empleado.setTelefono(rs.getString("telefono"));
                empleado.setCargo(rs.getString("cargo"));
                empleado.setUsuario(rs.getString("usuario"));
                empleado.setPassword(rs.getString("password"));
                empleado.setFechaRegistro(rs.getString("fecha_registro"));
                empleado.setActivo(rs.getBoolean("activo"));
            }

        } catch (SQLException e) {
            System.err.println("Error al buscar empleado por usuario: " + e.getMessage());
        }

        return empleado;
    }

    @Override
    public List<Empleado> obtenerTodos() {
        List<Empleado> empleados = new ArrayList<>();
        String sql = "SELECT * FROM empleados ORDER BY nombre";

        try (Connection conn = ConexionDB.getConexion();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                Empleado empleado = new Empleado();
                empleado.setId(rs.getInt("id"));
                empleado.setDni(rs.getString("dni"));
                empleado.setNombre(rs.getString("nombre"));
                empleado.setEmail(rs.getString("email"));
                empleado.setTelefono(rs.getString("telefono"));
                empleado.setCargo(rs.getString("cargo"));
                empleado.setUsuario(rs.getString("usuario"));
                empleado.setPassword(rs.getString("password"));
                empleado.setFechaRegistro(rs.getString("fecha_registro"));
                empleado.setActivo(rs.getBoolean("activo"));

                empleados.add(empleado);
            }

        } catch (SQLException e) {
            System.err.println("Error al obtener todos los empleados: " + e.getMessage());
        }

        return empleados;
    }

    @Override
    public boolean insertar(Empleado empleado) {
        String sql = "INSERT INTO empleados (dni, nombre, email, telefono, cargo, usuario, password, fecha_registro, activo) VALUES (?, ?, ?, ?, ?, ?, SHA2(?, 256), ?, ?)";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, empleado.getDni());
            pstmt.setString(2, empleado.getNombre());
            pstmt.setString(3, empleado.getEmail());
            pstmt.setString(4, empleado.getTelefono());
            pstmt.setString(5, empleado.getCargo());
            pstmt.setString(6, empleado.getUsuario());
            pstmt.setString(7, empleado.getPassword());
            pstmt.setString(8, empleado.getFechaRegistro());
            pstmt.setBoolean(9, empleado.isActivo());

            return pstmt.executeUpdate() > 0;

        } catch (SQLException e) {
            System.err.println("Error al insertar empleado: " + e.getMessage());
            return false;
        }
    }

    @Override
    public boolean actualizar(Empleado empleado) {
        String sql = "UPDATE empleados SET dni = ?, nombre = ?, email = ?, telefono = ?, cargo = ?, usuario = ?, activo = ? WHERE id = ?";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, empleado.getDni());
            pstmt.setString(2, empleado.getNombre());
            pstmt.setString(3, empleado.getEmail());
            pstmt.setString(4, empleado.getTelefono());
            pstmt.setString(5, empleado.getCargo());
            pstmt.setString(6, empleado.getUsuario());
            pstmt.setBoolean(7, empleado.isActivo());
            pstmt.setInt(8, empleado.getId());

            return pstmt.executeUpdate() > 0;

        } catch (SQLException e) {
            System.err.println("Error al actualizar empleado: " + e.getMessage());
            return false;
        }
    }

    @Override
    public boolean eliminar(int id) {
        String sql = "DELETE FROM empleados WHERE id = ?";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setInt(1, id);
            return pstmt.executeUpdate() > 0;

        } catch (SQLException e) {
            System.err.println("Error al eliminar empleado: " + e.getMessage());
            return false;
        }
    }
}
