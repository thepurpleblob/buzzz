function confirmation(urlyes,urlno,message) {
    var answer = confirm(message)
    if (answer){
        window.location = urlyes;
    }
    else{
        window.location = urlno;
    }
}
