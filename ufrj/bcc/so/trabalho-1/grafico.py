import turtle
import math
import random
turtle.resetscreen()

turtles = []

axis = turtle.Turtle()
axis.up()
axis.goto(-350, -250)
axis.down()
axis.left(90)
axis.speed(0)

for i in range(10):
    axis.forward(60)
    axis.dot()
    axis.left(90)
    axis.forward(25)
    axis.write(i+1)
    axis.forward(10)
    axis.write('P')
    axis.right(180)
    axis.forward(35)
    axis.left(90)
    
axis.goto(-350, -250)
axis.right(90)
axis.speed(3)
axis.forward(800)
axis.hideturtle()
    
for i in range(10):
    turtles.append(turtle.Turtle())
    turtles[i].up()
    turtles[i].goto(-340, -250+(i+1)*60)
    turtles[i].shape('circle')

for i in range(10):
    turtles[i].down()
    turtles[i].speed(6)
    turtles[i].forward(random.randint(100,500))
    turtles[i].write('Terminei',align='left')
    turtles[i].dot()
    turtles[i].hideturtle()
                     


