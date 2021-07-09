document.addEventListener('DOMContentLoaded', function(){
    let div = document.createElement('DIV');
    div.classList.add('snowContainer');
    div.innerHTML = "<div id='snow'></div>" ;
    document.body.appendChild(div);
})