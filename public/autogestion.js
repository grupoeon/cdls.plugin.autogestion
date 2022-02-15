(() => {
  const conditionField = (conditioner, conditionees) => {
    conditioner = document.querySelector(conditioner);
    if (!conditioner) return;

    const show = (selector) => {
      document
        .querySelector(selector)
        .closest(".fields > div")
        .style.removeProperty("display");
      document.querySelector(selector).removeAttribute("disabled");
    };
    const hide = (selector) => {
      document.querySelector(selector).closest(".fields > div").style.display =
        "none";
      document.querySelector(selector).setAttribute("disabled", true);
    };

    const conditionate = () => {
      const value = conditioner.value;
      const all = Object.values(conditionees).reduce((prev, curr) => {
        return prev.concat(curr);
      }, []);
      all.forEach(hide);
      let any = false;
      for (let [condition, conditionee] of Object.entries(conditionees)) {
        if (value == condition) {
          any = true;
          conditionee.forEach(show);
        }
      }
      if (!any && value != "" && conditionees.else) {
        conditionees.else.forEach(show);
      }
    };

    conditioner.addEventListener("change", conditionate);
    conditionate();
  };

  const setupPaymentsForm = () => {
    const form = document.querySelector("#pagos");

    if (!form) {
      return;
    }

    const publicApiKey = "df16c9dedd1c4df684abb8be4adcee27";
    //const publicApiKey = "96e7f0d36a0648fb9a8dcb50ac06d26096e7f0d36a0648fb9a8dcb50ac06d260";
    const urlSandbox = "https://live.decidir.com/api/v2";
    //Para el ambiente de desarrollo
    const decidir = new Decidir(urlSandbox);
    //Se indica la public API Key
    decidir.setPublishableKey(publicApiKey);
    decidir.setTimeout(5000); //timeout de 5 segundos
    //formulario
    //Asigna la funcion de invocacion al evento de submit del formulario
    form.addEventListener("submit", sendForm);
    //funcion para manejar la respuesta
    function sdkResponseHandler(status, response) {
      console.log(status);
      if (status != 200 && status != 201) {
        console.log("error");
        console.log(response);
      } else {
        console.log("success");
        console.log(response);
        form.querySelector("[name=token_decidir]").value = response.id;
        form.querySelector("[name=tarjeta_bin]").value = response.bin;
        form.submit();
      }
    }
    //funcion de invocacion con sdk
    function sendForm(event) {
      event.preventDefault();
      decidir.createToken(form, sdkResponseHandler); //formulario y callback
      return false;
    }
    //..codigo...
  };

  window.addEventListener("load", () => {
    conditionField("[name=id_tipo_documento]", {
      5: ["[name=nombre]", "[name=apellido]"],
      2: ["[name=razon_social]"],
    });

    conditionField("[name=id_medio_de_pago]", {
      9: ["[name=nro_cbu]"],
      else: [
        "[name=nro_tarjeta]",
        "[name=vencimiento_tarjeta_ano]",
        "[name=vencimiento_tarjeta_mes]",
      ],
    });

    setupPaymentsForm();
  });
})();
