UPDATE users 
SET password_hash = crypt('KorokNET', gen_salt('bf'))
WHERE login = 'Admin';

SELECT login, full_name, role FROM users WHERE login = 'Admin';


