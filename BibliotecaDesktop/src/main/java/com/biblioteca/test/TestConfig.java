package com.biblioteca.test;

import java.io.InputStream;
import java.util.Properties;

public class TestConfig {
    public static void main(String[] args) {
        System.out.println("TEST DE CARGA DE CONFIG.PROPERTIES");


        System.out.println("1. Usando ClassLoader:");
        try (InputStream input = TestConfig.class.getClassLoader()
                .getResourceAsStream("config.properties")) {

            if (input == null) {
                System.out.println("No se encontró config.properties en classpath");
                System.out.println("Asegúrate de que esté en src/main/resources/");
                return;
            }

            Properties prop = new Properties();
            prop.load(input);

            System.out.println("Archivo encontrado y cargado");
            System.out.println("URL: " + prop.getProperty("db.url"));
            System.out.println("Usuario: " + prop.getProperty("db.user"));
            System.out.println("Password: " +
                    (prop.getProperty("db.password").isEmpty() ? "(vacía)" : "***"));

        } catch (Exception e) {
            System.err.println("Error al cargar config.properties: " + e.getMessage());
            e.printStackTrace();
        }

        // Meodo 2: Usando ruta absoluta (para debug)
        System.out.println("2. Búsqueda en rutas comunes:");
        String[] rutas = {
                "src/main/resources/config.properties",
                "target/classes/config.properties",
                "./config.properties"
        };

        for (String ruta : rutas) {
            java.io.File file = new java.io.File(ruta);
            System.out.println("   • " + ruta + " -> " +
                    (file.exists() ? "EXISTE" : "NO EXISTE"));
        }
    }
}