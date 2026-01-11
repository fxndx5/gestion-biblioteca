package com.biblioteca.config;

public class DatabaseConfig {
    private String url = "jdbc:mysql://127.0.0.1:3306/gestion_biblioteca?useSSL=false&serverTimezone=UTC";
    private String user = "root";
    private String password = "";

    public String getUrl() { return url; }
    public String getUser() { return user; }
    public String getPassword() { return password; }

    public void printConfig() {
        System.out.println("CONFIGURACIÓN:");
        System.out.println("URL: " + url);
        System.out.println("Usuario: " + user);
        System.out.println("Password: " + (password.isEmpty() ? "(vacía)" : "***"));
    }
}