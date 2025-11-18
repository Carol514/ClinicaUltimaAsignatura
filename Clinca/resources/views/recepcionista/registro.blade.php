@extends('layouts.app')
@section('title', 'Registrar Paciente')

@section('content')
<main class="dashboard">
  <h2>Registrar nuevo paciente</h2>

  <div class="form-container">
    <form id="registroForm">
      <label>Nombre completo</label>
      <input id="full_name" type="text" placeholder="Ej. Juan Pérez" required>

      <label>Edad</label>
      <input id="age" type="number" min="0" max="120" placeholder="Ej. 34">

      <label>Sexo</label>
      <select id="sex" required>
        <option value="" disabled selected>Seleccione…</option>
        <option value="M">Masculino</option>
        <option value="F">Femenino</option>
        <option value="I">Otro / Indefinido</option>
      </select>

      <label>Dirección</label>
      <input id="address" type="text" placeholder="Calle, colonia, ciudad">

       <label>Correo</label>
      <input id="email" type="email" placeholder="ejemplo@correo.com">

      <label>Teléfono o contacto</label>
      <input id="phone" type="tel" placeholder="Ej. 3221234567">

      <div class="btn-container">
        <button type="submit" class="confirm-btn">Guardar paciente</button>
        <a href="{{ route('recepcionista.panel') }}" class="cancel-btn">Volver</a>
      </div>
    </form>
    <p id="registroMsg" class="muted" style="margin-top:8px;"></p>
  </div>

  <script>
  (function(){
    const form = document.getElementById('registroForm');
    const msg = document.getElementById('registroMsg');
    function setMsg(text, ok=true){ msg.textContent = text; msg.style.color = ok ? 'green' : 'crimson'; }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function postJson(url, payload){
      try{
        const res = await fetch(url, {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        if (!res || typeof res.headers === 'undefined'){
          throw new Error('Respuesta inválida del servidor (sin headers). Comprueba la conexión y el backend.');
        }

        if (res.status === 419) throw new Error('Sesión expirada (419). Recarga la página e inicia sesión.');
        if (res.status === 401) throw new Error('No autorizado (401). Inicia sesión.');
        if (res.redirected) throw new Error('La petición fue redirigida a ' + res.url + ' — posible falta de sesión.');

        const ct = (res.headers.get && res.headers.get('content-type')) || '';
        if (ct.includes('application/json')){
          const body = await res.clone().json().catch(()=>null);
          if (body !== null) return { status: res.status, body };
        }

        const text = await res.clone().text().catch(()=>null) || '';
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')){
          throw new Error('Respuesta HTML inesperada del servidor (posible redirección o error).');
        }
        return { status: res.status, body: text };

      }catch(err){
        if (err instanceof TypeError) throw new Error('Error de red o CORS: '+err.message);
        throw err;
      }
    }

    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      setMsg('');

  const full = document.getElementById('full_name')?.value.trim() || '';
  const phone = document.getElementById('phone')?.value.trim() || '';
  const address = document.getElementById('address')?.value.trim() || '';
  const sex = document.getElementById('sex')?.value || '';
  const email = document.getElementById('email')?.value?.trim() || '';
  const age = document.getElementById('age')?.value || '';
  const curp = document.getElementById('curp')?.value?.trim() || '';

      if (!full){ return setMsg('El nombre es requerido', false); }

      const parts = full.split(/\s+/);
      const first_name = parts.shift() || full;
      const last_name = parts.join(' ') || '';

      const payload = {
        first_name,
        last_name,
        phone,
        address,
        sex,
        curp,
        email: email || null,
        age: age ? parseInt(age, 10) : null
      };

      try{
        const res = await postJson('/recepcionista/api/patients', payload);

        if (res.status === 201 || res.status === 200){
          const data = res.body;
          if (typeof data === 'string') return setMsg('Respuesta inesperada: '+data, false);
          // show patient id and optionally created user credentials
          let msgText = 'Paciente guardado correctamente (ID: '+(data.data?.id||data.id||'')+')';
          if (data.created_user){
            msgText += ' — Usuario creado: '+(data.created_user.email||'')+' (contraseña temporal: '+(data.created_user.temp_password||'')+')';
          }
          setMsg(msgText);
          form.reset();
          return;
        }

        if (res.status === 422){
          const err = res.body;
          const messages = [];
          if (typeof err === 'string') return setMsg('Errores de validación: '+err, false);
          for (const k in err.errors || {}) messages.push((err.errors[k]||[]).join(', '));
          return setMsg(messages.join(' • ') || 'Errores de validación', false);
        }

        setMsg('Error al guardar paciente: ' + (res.status||'??') + ' ' + (typeof res.body === 'string' ? res.body : JSON.stringify(res.body || {})), false);

      }catch(ex){
        setMsg('Error de red: '+ (ex.message || ex), false);
      }
    });
  })();
  </script>
</main>
@endsection
