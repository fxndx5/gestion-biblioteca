package com.biblioteca.dao;

import com.biblioteca.model.Cliente;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ClienteDAOImpl implements ClienteDAO {

    @Override
    public boolean insertar(Cliente cliente) {
        String sql = "INSERT INTO clientes (dni, nombre, email, telefono, direccion, fecha_registro, sancionado) VALUES (?, ?, ?, ?, ?, ?, ?)";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, cliente.getDni());
            pstmt.setString(2, cliente.getNombre());
            pstmt.setString(3, cliente.getEmail());
            pstmt.setString(4, cliente.getTelefono());
            pstmt.setString(5, cliente.getDireccion());
            pstmt.setDate(6, Date.valueOf(cliente.getFechaRegistro()));
            pstmt.setBoolean(7, cliente.isSancionado());

            int filasAfectadas = pstmt.executeUpdate();
            return filasAfectadas > 0;

        } catch (SQLException e) {
            System.err.println("Error al insertar cliente: " + e.getMessage());
            return false;
        }
    }

    @Override
    public boolean actualizar(Cliente cliente) {
        String sql = "UPDATE clientes SET dni = ?, nombre = ?, email = ?, telefono = ?, direccion = ?, sancionado = ?, fecha_fin_sancion = ? WHERE id = ?";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, cliente.getDni());
            pstmt.setString(2, cliente.getNombre());
            pstmt.setString(3, cliente.getEmail());
            pstmt.setString(4, cliente.getTelefono());
            pstmt.setString(5, cliente.getDireccion());
            pstmt.setBoolean(6, cliente.isSancionado());

            if (cliente.getFechaFinSancion() != null) {
                pstmt.setDate(7, Date.valueOf(cliente.getFechaFinSancion()));
            } else {
                pstmt.setNull(7, Types.DATE);
            }

            pstmt.setInt(8, cliente.getId());

            return pstmt.executeUpdate() > 0;

        } catch (SQLException e) {
            System.err.println("Error al actualizar cliente: " + e.getMessage());
            return false;
        }
    }

    @Override
    public boolean eliminar(int id) {
        String sql = "DELETE FROM clientes WHERE id = ?";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setInt(1, id);
            return pstmt.executeUpdate() > 0;

        } catch (SQLException e) {
            System.err.println("Error al eliminar cliente: " + e.getMessage());
            return false;
        }
    }

    @Override
    public Cliente buscarPorId(int id) {
        String sql = "SELECT * FROM clientes WHERE id = ?";
        Cliente cliente = null;

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setInt(1, id);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                cliente = mapearCliente(rs);
            }

        } catch (SQLException e) {
            System.err.println("Error al buscar cliente por ID: " + e.getMessage());
        }

        return cliente;
    }

    @Override
    public Cliente buscarPorDni(String dni) {
        String sql = "SELECT * FROM clientes WHERE dni = ?";
        Cliente cliente = null;

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, dni);
            ResultSet rs = pstmt.executeQuery();

            if (rs.next()) {
                cliente = mapearCliente(rs);
            }

        } catch (SQLException e) {
            System.err.println("Error al buscar cliente por DNI: " + e.getMessage());
        }

        return cliente;
    }

    @Override
    public List<Cliente> obtenerTodos() {
        List<Cliente> clientes = new ArrayList<>();
        String sql = "SELECT * FROM clientes ORDER BY nombre";

        try (Connection conn = ConexionDB.getConexion();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                Cliente cliente = mapearCliente(rs);
                clientes.add(cliente);
            }

        } catch (SQLException e) {
            System.err.println("Error al obtener todos los clientes: " + e.getMessage());
        }

        return clientes;
    }

    @Override
    public List<Cliente> buscarPorNombre(String nombre) {
        List<Cliente> clientes = new ArrayList<>();
        String sql = "SELECT * FROM clientes WHERE nombre LIKE ? ORDER BY nombre";

        try (Connection conn = ConexionDB.getConexion();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, "%" + nombre + "%");
            ResultSet rs = pstmt.executeQuery();

            while (rs.next()) {
                Cliente cliente = mapearCliente(rs);
                clientes.add(cliente);
            }

        } catch (SQLException e) {
            System.err.println("Error al buscar clientes por nombre: " + e.getMessage());
        }

        return clientes;
    }

    /**
     * Metodo auxiliar para mapear un ResultSet a un objeto Cliente
     */
    private Cliente mapearCliente(ResultSet rs) throws SQLException {
        Cliente cliente = new Cliente();
        cliente.setId(rs.getInt("id"));
        cliente.setDni(rs.getString("dni"));
        cliente.setNombre(rs.getString("nombre"));
        cliente.setEmail(rs.getString("email"));
        cliente.setTelefono(rs.getString("telefono"));
        cliente.setDireccion(rs.getString("direccion"));
        cliente.setFechaRegistro(rs.getDate("fecha_registro").toLocalDate());
        cliente.setSancionado(rs.getBoolean("sancionado"));

        Date fechaFinSancion = rs.getDate("fecha_fin_sancion");
        if (fechaFinSancion != null && !rs.wasNull()) {
            cliente.setFechaFinSancion(fechaFinSancion.toLocalDate());
        }

        return cliente;
    }

    /**
     * Metodo  para obtener clientes sancionados
     */
    public List<Cliente> obtenerClientesSancionados() {
        List<Cliente> clientes = new ArrayList<>();
        String sql = "SELECT * FROM clientes WHERE sancionado = 1 ORDER BY nombre";

        try (Connection conn = ConexionDB.getConexion();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                Cliente cliente = mapearCliente(rs);
                clientes.add(cliente);
            }

        } catch (SQLException e) {
            System.err.println("Error al obtener clientes sancionados: " + e.getMessage());
        }

        return clientes;
    }

    /**
     * Metodo  para obtener clientes no sancionados
     */
    public List<Cliente> obtenerClientesNoSancionados() {
        List<Cliente> clientes = new ArrayList<>();
        String sql = "SELECT * FROM clientes WHERE sancionado = 0 ORDER BY nombre";

        try (Connection conn = ConexionDB.getConexion();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                Cliente cliente = mapearCliente(rs);
                clientes.add(cliente);
            }

        } catch (SQLException e) {
            System.err.println("Error al obtener clientes no sancionados: " + e.getMessage());
        }

        return clientes;
    }
}