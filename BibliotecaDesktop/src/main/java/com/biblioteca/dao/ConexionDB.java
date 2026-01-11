package com.biblioteca.dao;

import com.biblioteca.config.DatabaseConfig;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class ConexionDB {
    private static Connection conexion = null;
    private static final DatabaseConfig config = new DatabaseConfig();

    public static Connection getConexion() {
        try {
            // Verificar si la conexión existe Y está activa
            if (conexion == null || conexion.isClosed()) {
                Class.forName("com.mysql.cj.jdbc.Driver");

                conexion = DriverManager.getConnection(
                        config.getUrl(),
                        config.getUser(),
                        config.getPassword()
                );

                System.out.println("Conexión establecida con MariaDB");
                System.out.println("URL: " + config.getUrl());
                System.out.println("Usuario: " + config.getUser());
            }
        } catch (ClassNotFoundException e) {
            System.err.println("Error: Driver MySQL no encontrado");
            e.printStackTrace();
        } catch (SQLException e) {
            System.err.println("Error al conectar a MariaDB:");
            System.err.println("Mensaje: " + e.getMessage());
            diagnosticarError(e);
        }
        return conexion;
    }

    private static void diagnosticarError(SQLException e) {
        System.out.println("\n=== DIAGNÓSTICO DE ERROR ===");
        System.out.println("1. ✓ Verifica que XAMPP esté ejecutándose");
        System.out.println("2. ✓ Verifica que MySQL esté en 'Running'");
        System.out.println("3. ✓ Verifica que exista la BD 'gestion_biblioteca'");
        System.out.println("4. ✓ Usuario 'root' sin contraseña (XAMPP default)");
        System.out.println("5. ✓ Puerto 3306 disponible");
    }

    // NO uses este método en producción, solo para testing
    public static void cerrarConexion() {
        if (conexion != null) {
            try {
                conexion.close();
                conexion = null;
                System.out.println("Conexión cerrada correctamente");
            } catch (SQLException e) {
                System.err.println("Error al cerrar conexión: " + e.getMessage());
            }
        }
    }

    public static void testConexion() {
        try {
            Connection testConn = getConexion();
            if (testConn != null && !testConn.isClosed()) {
                System.out.println("✓ Test de conexión: EXITOSO");
                System.out.println("  Base de datos: " + testConn.getCatalog());

                var meta = testConn.getMetaData();
                System.out.println("  Versión: " + meta.getDatabaseProductVersion());
            }
        } catch (SQLException e) {
            System.err.println("✗ Test de conexión: FALLIDO");
            e.printStackTrace();
        }
    }
}