'use strict';

const e = React.createElement;

class NameForm extends React.Component {
    constructor(props) {
      super(props);
  
      this.handleChange = this.handleChange.bind(this);
      this.handleSubmit = this.handleSubmit.bind(this);

      this.state = {
        isLoading: false,
        value: null
      };
    }
  
    handleChange(event) {
        this.setState({
            isLoading: false,
            value: event.target.value
        });
    }
  
    handleSubmit(event) {
        event.preventDefault();

        if(this.state.isLoading) {
            return ;
        }
        var data = new FormData();
        var filedata = document.querySelector('input[type="file"]').files[0];
        data.append("file", filedata);
        
        if(this.state.value) {
            this.setState({
                isLoading: true
            });
            document.querySelector('#result').innerHTML = '';
            
            fetch("/converter.php", {
                method: "POST",
                body: data
            })
            .then(res => res.json())
            .then(
                (result) => {
                    this.setState({
                        isLoading: false
                    });

                    if(result.status == 'error') {
                        document.querySelector('#result').innerHTML = `<div role="alert" class="fade alert alert-danger show">${result.message}</div>`;
                    }

                    if(result.status == 'success') {
                        document.querySelector('#result').innerHTML = `<div style="display:flex;grid-gap:10px;flex-wrap:wrap;">${result.items}</div>`;
                    }
                },
                // Примечание: важно обрабатывать ошибки именно здесь, а не в блоке catch(),
                // чтобы не перехватывать исключения из ошибок в самих компонентах.
                (error) => {
                    this.setState({
                        isLoading: false
                    });
                    document.querySelector('#result').innerHTML = `<div role="alert" class="fade alert alert-danger show">${error}</div>`;
                }
            );


        }
    }
  
    render() {
        return React.createElement("form", { onSubmit: this.handleSubmit }, 
            React.createElement("div", { className: "form-group mb-3" }, 
                React.createElement("label", { htmlFor: "file", className: "form-label" }, "Загрузите файл pdf, ppt или pptx"), 
                React.createElement("input", { onChange: this.handleChange, id: "file", name: "file", type: "file", className: "form-control", required: true })
            ), 
            React.createElement("div", { className: "form-group" }, 
                React.createElement("button", 
                    { className: "btn btn-outline-danger", type: "submit", disabled: this.state.isLoading }, 
                    this.state.isLoading ? 'Конвертация…' : "Конвертировать"
                )
            )
        );
    }
}
const domContainer = document.querySelector('#form');
ReactDOM.render(e(NameForm), domContainer);