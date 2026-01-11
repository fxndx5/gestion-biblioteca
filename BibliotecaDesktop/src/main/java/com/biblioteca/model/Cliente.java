package com.biblioteca.model;

import java.time.LocalDate;

public class Cliente {
    private int id;
    private String dni;
    private String nombre;
    private String email;
    private String telefono;
    private String direccion;
    private LocalDate fechaRegistro;
    private boolean sancionado;
    private LocalDate fechaFinSancion;

    public Cliente() {}

    public Cliente(String text, String text1, String text2, String text3, String text4) {
    }

    public int getId() { return id; }
    public void setId(int id) { this.id = id; }

    public String getDni() { return dni; }
    public void setDni(String dni) { this.dni = dni; }

    public String getNombre() { return nombre; }
    public void setNombre(String nombre) { this.nombre = nombre; }

    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }

    public String getTelefono() { return telefono; }
    public void setTelefono(String telefono) { this.telefono = telefono; }

    public String getDireccion() { return direccion; }
    public void setDireccion(String direccion) { this.direccion = direccion; }

    public LocalDate getFechaRegistro() { return fechaRegistro; }
    public void setFechaRegistro(LocalDate fechaRegistro) { this.fechaRegistro = fechaRegistro; }

    public boolean isSancionado() { return sancionado; }
    public void setSancionado(boolean sancionado) { this.sancionado = sancionado; }

    public LocalDate getFechaFinSancion() { return fechaFinSancion; }
    public void setFechaFinSancion(LocalDate fechaFinSancion) { this.fechaFinSancion = fechaFinSancion; }
}
