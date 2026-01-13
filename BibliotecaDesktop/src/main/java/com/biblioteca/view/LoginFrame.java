package com.biblioteca.view;

import com.biblioteca.controller.LoginController;
import javax.swing.*;
import java.awt.*;
import javax.swing.border.EmptyBorder;

public class LoginFrame extends JFrame {
    private LoginController controller;
    private JTextField txtUsuario;
    private JPasswordField txtPassword;
    private JLabel lblMensaje;

    // paleta colores
    private final Color COLOR_PRIMARIO = new Color(25, 118, 210);
    private final Color COLOR_SECUNDARIO = new Color(13, 71, 161);
    private final Color COLOR_TEXTO_OSCURO = new Color(60, 60, 60);
    private final Color COLOR_TEXTO_MEDIO = new Color(80, 80, 80);
    private final Color COLOR_BORDE = new Color(200, 200, 200);
    private final Color COLOR_FONDO_FORMULARIO = Color.WHITE;

    public LoginFrame() {
        setTitle("Sistema de Gestión de Biblioteca");
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        setSize(1000, 700);
        setLocationRelativeTo(null);
        setResizable(false);

        // Establecer icono de la ventana
        try {
            ImageIcon icon = new ImageIcon(getClass().getResource("/com/biblioteca/images/logo-biblioteca.png"));
            setIconImage(icon.getImage());
        } catch (Exception e) {
            System.out.println("Icono no encontrado, usando por defecto");
        }

        controller = new LoginController();
        initComponents();
    }

    private void initComponents() {
        // Panel principal con layout de borde
        JPanel mainPanel = new JPanel(new BorderLayout());

        // Panel izquierdo con imagen de fondo
        JPanel leftPanel = new JPanel() {
            @Override
            protected void paintComponent(Graphics g) {
                super.paintComponent(g);
                try {
                    // Cargar imagen como fondo
                    ImageIcon icon = new ImageIcon(getClass().getResource("/com/biblioteca/images/logo-biblioteca.png"));
                    Image img = icon.getImage();
                    // Escalar imagen para que cubra el panel
                    g.drawImage(img, 0, 0, getWidth(), getHeight(), this);
                } catch (Exception e) {
                    // Si no hay imagen, usar gradiente azul
                    Graphics2D g2d = (Graphics2D) g;
                    GradientPaint gradient = new GradientPaint(
                            0, 0, COLOR_PRIMARIO,
                            getWidth(), getHeight(), COLOR_SECUNDARIO
                    );
                    g2d.setPaint(gradient);
                    g2d.fillRect(0, 0, getWidth(), getHeight());

                    // Texto alternativo
                    g2d.setColor(Color.WHITE);
                    g2d.setFont(new Font("Arial", Font.BOLD, 24));
                    String text = "SISTEMA DE BIBLIOTECA";
                    FontMetrics fm = g2d.getFontMetrics();
                    int x = (getWidth() - fm.stringWidth(text)) / 2;
                    int y = getHeight() / 2;
                    g2d.drawString(text, x, y);
                }
            }
        };
        leftPanel.setPreferredSize(new Dimension(500, 700));

        // Panel derecho con formulario de login
        JPanel rightPanel = new JPanel(new GridBagLayout());
        rightPanel.setBackground(new Color(248, 249, 250));

        GridBagConstraints gbc = new GridBagConstraints();
        gbc.insets = new Insets(15, 15, 15, 15);
        gbc.fill = GridBagConstraints.HORIZONTAL;

        // Título principal - Color azul oscuro para mejor visibilidad
        JLabel lblTitulo = new JLabel("BIBLIOTECA PÚBLICA");
        lblTitulo.setFont(new Font("Segoe UI", Font.BOLD, 28));
        lblTitulo.setForeground(COLOR_SECUNDARIO);
        lblTitulo.setHorizontalAlignment(SwingConstants.CENTER);

        // Subtítulo - Color gris medio
        JLabel lblSubtitulo = new JLabel("Sistema de Gestión Integral");
        lblSubtitulo.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        lblSubtitulo.setForeground(COLOR_TEXTO_MEDIO);
        lblSubtitulo.setHorizontalAlignment(SwingConstants.CENTER);

        // Panel del formulario
        JPanel formPanel = new JPanel(new GridBagLayout());
        formPanel.setBackground(COLOR_FONDO_FORMULARIO);
        formPanel.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(30, 30, 30, 30)
        ));

        GridBagConstraints gbcForm = new GridBagConstraints();
        gbcForm.insets = new Insets(10, 10, 10, 10);
        gbcForm.fill = GridBagConstraints.HORIZONTAL;

        JLabel lblFormTitle = new JLabel("INICIAR SESIÓN");
        lblFormTitle.setFont(new Font("Segoe UI", Font.BOLD, 18));
        lblFormTitle.setForeground(COLOR_SECUNDARIO);
        lblFormTitle.setHorizontalAlignment(SwingConstants.CENTER);

        gbcForm.gridx = 0;
        gbcForm.gridy = 0;
        gbcForm.gridwidth = 2;
        formPanel.add(lblFormTitle, gbcForm);

        // Campo Usuario
        gbcForm.gridx = 0;
        gbcForm.gridy = 1;
        gbcForm.gridwidth = 2;
        JLabel lblUsuario = new JLabel("Usuario:");
        lblUsuario.setFont(new Font("Segoe UI", Font.BOLD, 12));
        lblUsuario.setForeground(COLOR_TEXTO_OSCURO); // Texto más oscuro
        formPanel.add(lblUsuario, gbcForm);

        gbcForm.gridx = 0;
        gbcForm.gridy = 2;
        gbcForm.gridwidth = 2;
        txtUsuario = new JTextField(20);
        txtUsuario.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        txtUsuario.setForeground(COLOR_TEXTO_OSCURO); // Texto del input oscuro
        txtUsuario.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(10, 10, 10, 10)
        ));
        formPanel.add(txtUsuario, gbcForm);

        // Campo Contraseña
        gbcForm.gridx = 0;
        gbcForm.gridy = 3;
        gbcForm.gridwidth = 2;
        JLabel lblPassword = new JLabel("Contraseña:");
        lblPassword.setFont(new Font("Segoe UI", Font.BOLD, 12));
        lblPassword.setForeground(COLOR_TEXTO_OSCURO); // Texto más oscuro
        formPanel.add(lblPassword, gbcForm);

        gbcForm.gridx = 0;
        gbcForm.gridy = 4;
        gbcForm.gridwidth = 2;
        txtPassword = new JPasswordField(20);
        txtPassword.setFont(new Font("Segoe UI", Font.PLAIN, 14));
        txtPassword.setForeground(COLOR_TEXTO_OSCURO); // Texto del input oscuro
        txtPassword.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(COLOR_BORDE, 1),
                BorderFactory.createEmptyBorder(10, 10, 10, 10)
        ));
        formPanel.add(txtPassword, gbcForm);

        // Botón Login
        gbcForm.gridx = 0;
        gbcForm.gridy = 5;
        gbcForm.gridwidth = 2;
        JButton btnLogin = new JButton("INICIAR SESIÓN");
        btnLogin.setFont(new Font("Segoe UI", Font.BOLD, 14));
        btnLogin.setBackground(COLOR_PRIMARIO);
        btnLogin.setBorder(BorderFactory.createEmptyBorder(12, 30, 12, 30));
        btnLogin.setFocusPainted(false);
        btnLogin.setCursor(new Cursor(Cursor.HAND_CURSOR));

        // Efecto hover para el botón
        btnLogin.addMouseListener(new java.awt.event.MouseAdapter() {
            public void mouseEntered(java.awt.event.MouseEvent evt) {
                btnLogin.setBackground(new Color(21, 101, 192));
            }
            public void mouseExited(java.awt.event.MouseEvent evt) {
                btnLogin.setBackground(COLOR_PRIMARIO);
            }
        });

        btnLogin.addActionListener(e -> {
            String usuario = txtUsuario.getText();
            String password = new String(txtPassword.getPassword());

            if (controller.autenticar(usuario, password)) {
                DashboardFrame dashboard = new DashboardFrame();
                dashboard.setVisible(true);
                this.dispose();
            } else {
                JOptionPane.showMessageDialog(this,
                        "Usuario o contraseña incorrectos",
                        "Error de autenticación",
                        JOptionPane.ERROR_MESSAGE);
            }
        });
        formPanel.add(btnLogin, gbcForm);

        // Enlace para recuperar contraseña
        gbcForm.gridx = 0;
        gbcForm.gridy = 6;
        gbcForm.gridwidth = 2;
        JLabel lblRecuperar = new JLabel("<html><u>¿Olvidó su contraseña?</u></html>");
        lblRecuperar.setFont(new Font("Segoe UI", Font.PLAIN, 12));
        lblRecuperar.setForeground(COLOR_TEXTO_MEDIO); // Color gris medio
        lblRecuperar.setCursor(new Cursor(Cursor.HAND_CURSOR));
        lblRecuperar.setHorizontalAlignment(SwingConstants.CENTER);
        formPanel.add(lblRecuperar, gbcForm);

        // Layout para el panel derecho
        gbc.gridx = 0;
        gbc.gridy = 0;
        gbc.weighty = 0.1;
        rightPanel.add(lblTitulo, gbc);

        gbc.gridy = 1;
        gbc.weighty = 0.05;
        rightPanel.add(lblSubtitulo, gbc);

        gbc.gridy = 2;
        gbc.weighty = 0.7;
        gbc.fill = GridBagConstraints.BOTH;
        rightPanel.add(formPanel, gbc);

        // Agregar paneles al main
        mainPanel.add(leftPanel, BorderLayout.WEST);
        mainPanel.add(rightPanel, BorderLayout.CENTER);

        add(mainPanel);

        // Permitir login con Enter
        txtPassword.addActionListener(e -> btnLogin.doClick());

        // Establecer foco en el campo de usuario al iniciar
        SwingUtilities.invokeLater(() -> txtUsuario.requestFocus());
    }
}